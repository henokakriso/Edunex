#!/bin/bash
# Full page/link audit: every route the role's nav can reach must return a page.
# Accept 200 or 302 (by-design redirects like dashboard dispatcher / param-less pages).
# FAIL on: 404, 403, 500, or "coming soon" placeholder content.
BASE="${1:-http://127.0.0.1:8080/index.php?r=}"
DIR=$(mktemp -d)
PASS=0; FAIL=0
declare -a FAILED

login() { # $1 cookie, $2 identifier
  local cj="$DIR/$1.cj" tok
  tok=$(curl -s -m 15 -c "$cj" -b "$cj" "${BASE}auth/login" | grep -oP 'name="_csrf" value="\K[^"]+' | head -1)
  curl -s -m 15 -c "$cj" -b "$cj" "${BASE}auth/login" --data-urlencode "_csrf=$tok" \
    --data-urlencode "identifier=$2" --data-urlencode "password=Passw0rd!" -o /dev/null
}

chk() { # $1 name, $2 cookie file (or -), $3 route
  local name="$1" cj="$2" route="$3" code body
  if [ "$cj" = "-" ]; then
    code=$(curl -s -m 15 -o /tmp/opencode/audit_body.html -w "%{http_code}" "${BASE}${route}")
  else
    code=$(curl -s -m 15 -b "$DIR/$cj.cj" -o /tmp/opencode/audit_body.html -w "%{http_code}" "${BASE}${route}")
  fi
  body=$(grep -c -i "coming soon\|under construction" /tmp/opencode/audit_body.html 2>/dev/null)
  if { [ "$code" = "200" ] || [ "$code" = "302" ]; } && [ "$body" -eq 0 ]; then PASS=$((PASS+1)); else
    FAIL=$((FAIL+1)); FAILED+=("$name -> $code (want 200/302)")
  fi
}

echo "=== PUBLIC (guest) ==="
for p in "" "landing" "landing/home" "landing/features" "landing/ai" "landing/pricing" "landing/faq" "landing/contact" "auth/login" "auth/register" "auth/forgot" "auth/verify" "certificates/verify" "courses" "courses/view&id=1" "library" "library/item&id=1" "gamification/leaderboard" "gamification/badges" "search&q=math"; do
  chk "guest/$p" - "$p"
done

echo "=== ADMIN (nav-accurate) ==="
login admin "admin@edunex.local"
for p in "dashboard" "admin/users" "admin/user&id=2" "admin/schools" "admin/departments" "admin/subjects" "admin/groups" "admin/courses" "admin/years" "admin/transfers" "admin/announcements" "admin/library" "admin/reports" "admin/analytics" "admin/logs" "admin/backups" "admin/roles" "admin/settings" "admin/ledger" "courses" "courses/view&id=1" "library" "library/item&id=1" "messages" "communication/groups" "communication/announcements" "notifications" "calendar" "gamification" "search&q=math" "files" "settings/profile" "settings/password" "settings/security" "settings/notifications" "settings/preferences" "settings/sessions"; do
  chk "admin/$p" admin "$p"
done

echo "=== DIRECTOR (nav-accurate) ==="
login director "director@edunex.local"
for p in "dashboard" "director/teachers" "director/students" "director/import" "director/transfers" "director/reports" "director/analytics" "library" "courses" "ai/tutor" "ai/assistant" "messages" "communication/groups" "communication/announcements" "notifications" "calendar" "gamification" "search&q=math" "files" "settings/profile" "settings/password" "settings/security" "settings/notifications" "settings/preferences" "settings/sessions"; do
  chk "director/$p" director "$p"
done

echo "=== TEACHER (nav-accurate) ==="
login teacher "teacher@edunex.local"
for p in "dashboard" "teacher/verify" "teacher/courses" "teacher/exams" "teacher/assignments" "teacher/attendance" "teacher/students" "teacher/import" "teacher/grade" "teacher/forum" "teacher/library" "teacher/analytics" "teacher/reports" "ai/tutor" "ai/assistant" "ai/history" "ai/flashcards" "ai/quiz" "courses" "courses/view&id=1" "library" "library/item&id=1" "messages" "communication/groups" "communication/announcements" "notifications" "calendar" "gamification" "gamification/badges" "gamification/leaderboard" "search&q=math" "files" "settings/profile" "settings/password" "settings/security" "settings/notifications" "settings/preferences" "settings/sessions"; do
  chk "teacher/$p" teacher "$p"
done

echo "=== STUDENT (nav-accurate) ==="
login student "student@edunex.local"
for p in "dashboard" "student/courses" "student/exams" "student/assignments" "student/attendance" "student/grades" "student/schedule" "ai/tutor" "ai/flashcards" "ai/assistant" "ai/history" "ai/quiz" "library" "student/leaderboard" "gamification" "certificates" "transfers" "courses" "courses/view&id=1" "messages" "communication/groups" "communication/announcements" "notifications" "calendar" "analytics/student" "search&q=math" "files" "settings/profile" "settings/password" "settings/security" "settings/notifications" "settings/preferences" "settings/sessions"; do
  chk "student/$p" student "$p"
done

echo "=== PARENT (nav-accurate) ==="
login parent "parent@edunex.local"
for p in "dashboard" "parent/children" "parent/reports" "analytics/student" "library" "certificates" "transfers" "courses" "courses/view&id=1" "messages" "communication/groups" "communication/announcements" "notifications" "calendar" "gamification" "search&q=math" "files" "settings/profile" "settings/password" "settings/security" "settings/notifications" "settings/preferences" "settings/sessions"; do
  chk "parent/$p" parent "$p"
done

echo "----------------------------------------"
echo "AUDIT PASS: $PASS   FAIL: $FAIL"
for f in "${FAILED[@]}"; do echo "  FAILED: $f"; done
rm -rf "$DIR"
