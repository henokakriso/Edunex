#!/usr/bin/env bash
# Link audit: log in as each role, crawl every page link, report non-200.
BASE="${BASE:-http://127.0.0.1:8080}"
UA="Mozilla/5.0"
declare -A RESULTS
declare -A VISITED
: > /tmp/opencode/audit-results.txt

login() { # $1 cookie, $2 identifier
  local cj=$1 id=$2
  curl -s -m 10 -c "$cj" -o /dev/null "$BASE/index.php?r=auth/login"
  local T=$(curl -s -m 10 -b "$cj" -c "$cj" "$BASE/index.php?r=auth/login" | grep -oP 'name="_csrf" value="\K[^"]+' | head -1)
  curl -s -m 10 -b "$cj" -c "$cj" -o /dev/null "$BASE/index.php?r=auth/login" \
    --data-urlencode "_csrf=$T" --data-urlencode "identifier=$id" --data-urlencode "password=Passw0rd!"
}

check() { # $1 cookie, $2 path
  local cj=$1 p=$2
  [ -n "${VISITED[$p]}" ] && return
  VISITED[$p]=1
  local code=$(curl -s -m 15 -b "$cj" -c "$cj" -o /dev/null -w "%{http_code}" "$BASE/index.php?r=$p")
  if [ "$code" != "200" ]; then
    echo "  [$code] $p" >> /tmp/opencode/audit-results.txt
  fi
  # extract links from the page and check them too (breadth-first, depth 2)
  local links=$(curl -s -m 15 -b "$cj" -c "$cj" "$BASE/index.php?r=$p" | grep -oP 'index.php\?r=\K[^"&]+' | sed 's/&[^=]*=[^&]*//g' | sort -u | head -80)
  for l in $links; do
    check "$cj" "$l"
  done
}

audit() { # $1 label, $2 cookie, $3 path
  echo "== $1 ==" >> /tmp/opencode/audit-results.txt
  check "$2" "$3"
}

rm -f /tmp/opencode/a_*.cj
login /tmp/opencode/a_admin.cj admin@edunex.local
login /tmp/opencode/a_teacher.cj teacher@edunex.local
login /tmp/opencode/a_student.cj student@edunex.local
login /tmp/opencode/a_parent.cj parent@edunex.local

audit "admin" /tmp/opencode/a_admin.cj admin/dashboard
audit "teacher" /tmp/opencode/a_teacher.cj teacher/dashboard
audit "student" /tmp/opencode/a_student.cj student/dashboard
audit "parent" /tmp/opencode/a_parent.cj parent/dashboard

audit "public" /tmp/opencode/a_student.cj ""
audit "courses" /tmp/opencode/a_student.cj courses
audit "library" /tmp/opencode/a_student.cj library
audit "search" /tmp/opencode/a_student.cj search
