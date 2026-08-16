#!/usr/bin/env bash
# EDUNEX end-to-end test sweep
# Usage: bash tests/e2e.sh [BASE_URL] [PHP_SERVER_PID]
set -u
BASE="${1:-http://127.0.0.1:8080/?r=}"
DIR="$(mktemp -d)"
PASS=0; FAIL=0; FAILED_NAMES=""

ck() { # ck <name> <expected_code> <url> [cookie_file] [data]
  local name="$1" exp="$2" url="$3" ckf="${4:-}"
  local data="${5:-}" code
  if [ -n "$data" ]; then
    code=$(curl -s -m 15 ${ckf:+-b "$ckf" -c "$ckf"} -o /dev/null -w "%{http_code}" -d "$data" "$BASE$url")
  elif [ -n "$ckf" ]; then
    code=$(curl -s -m 15 -b "$ckf" -c "$ckf" -o /dev/null -w "%{http_code}" "$BASE$url")
  else
    code=$(curl -s -m 15 -o /dev/null -w "%{http_code}" "$BASE$url")
  fi
  if [ "$code" = "$exp" ]; then PASS=$((PASS+1)); echo "  ok   $name ($code)";
  else FAIL=$((FAIL+1)); FAILED_NAMES="$FAILED_NAMES $name"; echo "  FAIL $name -> got $code, want $exp"; fi
}

login() { # login <cookie> <ident> <pass>
  local ckf="$1" ident="$2" pw="$3"
  curl -s -m 15 -c "$ckf" -b "$ckf" -o /dev/null "$BASE"auth/login
  local tok
  tok=$(curl -s -m 15 -c "$ckf" -b "$ckf" "$BASE"auth/login | grep -oP 'name="_csrf" value="\K[^"]+')
  curl -s -m 15 -c "$ckf" -b "$ckf" -o /dev/null -d "identifier=$ident&password=$pw&_csrf=$tok" "$BASE"auth/login
}

summary() {
  echo "=============================="
  echo "PASS: $PASS   FAIL: $FAIL"
  if [ -n "$FAILED_NAMES" ]; then echo "Failed:$FAILED_NAMES"; fi
  rm -rf "$DIR"
  [ "$FAIL" = "0" ] && exit 0 || exit 1
}

echo "=== PUBLIC PAGES ==="
ck "landing home" 200 ""
ck "landing features" 200 "landing/features"
ck "landing ai" 200 "landing/ai"
ck "landing pricing" 200 "landing/pricing"
ck "landing faq" 200 "landing/faq"
ck "landing contact" 200 "landing/contact"
ck "auth login" 200 "auth/login"
ck "auth register" 200 "auth/register"
ck "auth forgot" 200 "auth/forgot"
ck "auth otp" 200 "auth/otp"
ck "auth 2fa (redirects w/o session)" 302 "auth/2fa"
ck "courses browse (login req)" 302 "courses"
ck "courses view (login req)" 302 "courses/view&id=1"
ck "certificates verify" 200 "certificates/verify"
ck "certificates verify post" 200 "certificates/verify" "" "code=CERT-TEST"

echo "=== API LOGIN (desktop app) ==="
ck "api login ok" 200 "api/login" "" '{"identifier":"admin@edunex.local","password":"Passw0rd!"}'
ck "api login bad (spare account)" 401 "api/login" "" '{"identifier":"parent@edunex.local","password":"wrong"}'
ck "api login admin2 ok" 200 "api/login" "" '{"identifier":"admin2@edunex.local","password":"Passw0rd!"}'

echo "=== ADMIN ==="
AC="$DIR/admin.cj"
login "$AC" "admin@edunex.local" "Passw0rd!"
for p in "dashboard" "users" "user&id=2" "schools" "departments" "subjects" "groups" "years" "courses" "roles" "settings" "logs" "analytics" "reports" "backups" "announcements" "library" "transfers" "system" "ledger&school=1" "security&school=1"; do
  ck "admin/$p" 200 "admin/$p" "$AC"
done
ck "admin logs page" 200 "admin/logs" "$AC"
# CSRF-protected create user
ATOK=$(curl -s -m 15 -b "$AC" -c "$AC" "$BASE"admin/users | grep -oP 'name="_csrf" value="\K[^"]+' | head -1)
ck "admin create user (csrf)" 302 "admin/users" "$AC" "create_user=1&_csrf=$ATOK&first_name=Test&last_name=User&email=test.user.$(date +%s)@edunex.local&role=teacher&password=Passw0rd!"

echo "=== DIRECTOR ==="
DC="$DIR/director.cj"
login "$DC" "director@edunex.local" "Passw0rd!"
for p in "dashboard" "teachers" "students" "import" "transfers"; do
  ck "director/$p" 200 "director/$p" "$DC"
done

echo "=== TEACHER ==="
TC="$DIR/teacher.cj"
login "$TC" "teacher@edunex.local" "Passw0rd!"
for p in "dashboard" "courses" "course&id=1" "exams" "exam&id=1" "assignments" "assignment&id=1" "attendance" "students" "reports" "analytics" "forum" "library" "book" "verify"; do
  ck "teacher/$p" 200 "teacher/$p" "$TC"
done
# redirects when nothing owned/selected are fine
ck "teacher lesson (no access)" 302 "teacher/lesson&id=1" "$TC"
ck "teacher grade (nothing pending)" 302 "teacher/grade" "$TC"

echo "=== STUDENT ==="
SC="$DIR/student.cj"
login "$SC" "student@edunex.local" "Passw0rd!"
for p in "dashboard" "courses" "exams" "grades" "attendance" "schedule" "leaderboard" "assignments"; do
  ck "student/$p" 200 "student/$p" "$SC"
done

echo "=== PARENT ==="
PC="$DIR/parent.cj"
login "$PC" "parent@edunex.local" "Passw0rd!"
for p in "dashboard" "children" "reports"; do
  ck "parent/$p" 200 "parent/$p" "$PC"
done

echo "=== CROSS-ROLE MODULES (student) ==="
for p in "courses" "courses/view&id=1" "courses/learn&id=1&lesson=1" "courses/discuss&course=1" "courses/discuss&course=1&topic=1" "ai/tutor" "ai/assistant" "ai/history" "ai/flashcards" "ai/quiz" "library" "library/item&id=1" "messages" "communication/groups" "communication/announcements" "notifications" "calendar" "analytics/student" "certificates" "gamification" "gamification/badges" "gamification/leaderboard" "search&q=math" "settings/profile" "settings/password" "settings/security" "settings/notifications" "settings/preferences" "settings/sessions" "transfers" "transfers/new"; do
  ck "cross/$p" 200 "$p" "$SC"
done
# Files is hidden from students (teacher/admin/director/parent only)
ck "cross/files (student blocked)" 403 "files" "$SC"
# redirects when no certificate/attempt exist
ck "cross courses/certificate" 302 "courses/certificate" "$SC"
ck "cross exams/take (no attempt)" 302 "exams/take&id=1" "$SC"
ck "cross exams/result (no result)" 302 "exams/result&id=1" "$SC"
ck "cross certificates/view (bad code)" 302 "certificates/view&code=TEST" "$SC"
ck "cross assignments/view (not found)" 302 "assignments/view&id=99999" "$SC"

echo "=== API ENDPOINTS ==="
ck "api login ok" 200 "api/login" "" '{"identifier":"admin@edunex.local","password":"Passw0rd!"}'
ck "api login bad (spare account)" 401 "api/login" "" '{"identifier":"parent@edunex.local","password":"wrong"}'
TOKEN=$(curl -s -m 15 "$BASE"api/login -d '{"identifier":"student@edunex.local","password":"Passw0rd!"}' | grep -oP '"token":"\K[^"]+')
echo "  token: ${TOKEN:0:12}..."
echo "--- without token (must be 401) ---"
for p in "api/notifications/poll" "api/courses" "api/calendar" "api/library" "api/gamification" "api/attendance-mobile" "api/users&limit=5" "api/search&q=math" "api/activity"; do
  ck "api noauth GET $p" 401 "$p" "" "{}"
done
echo "--- with auth header ---"
for p in "api/notifications/poll" "api/courses" "api/gamification" "api/attendance-mobile" "api/calendar"; do
  code=$(curl -s -m 15 -o /dev/null -w "%{http_code}" -H "Authorization: Bearer $TOKEN" "$BASE$p")
  if [ "$code" = "200" ]; then PASS=$((PASS+1)); echo "  ok   api-auth/$p ($code)"; else FAIL=$((FAIL+1)); FAILED_NAMES="$FAILED_NAMES api-auth/$p"; echo "  FAIL api-auth/$p -> $code"; fi
done

summary
