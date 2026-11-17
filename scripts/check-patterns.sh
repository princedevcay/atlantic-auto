#!/usr/bin/env bash
# Fails the build on patterns that reintroduce SQL injection or XSS (engineering.vulnerability_class_reduction).
set -euo pipefail
fail=0

# 1. Raw PDO query/exec outside the Db wrapper and migrations.
if grep -rnE --include='*.php' -e '->(query|exec)\(' app bin | grep -vE '^app/Core/Db.php:|^bin/migrate.php:'; then
  echo "::error::Use Db::run with bound parameters instead of query()/exec()"; fail=1
fi

# 2. Variables interpolated into SQL strings ("SELECT ... $x" or 'SELECT ...' . $x).
if grep -rnE '"(SELECT|INSERT|UPDATE|DELETE)[^"]*\$[a-zA-Z_]' app --include='*.php'; then
  echo "::error::Do not interpolate variables into SQL"; fail=1
fi

# 3. Unescaped output in views: a bare variable may not be printed; wrap it in e(). ($content is pre-rendered HTML.)
if find app/views -name '*.php' -print0 | xargs -0 perl -ne 'print "$ARGV:$.: $_" if /<\?=\s*\$(?!content\b)[a-zA-Z_]+(\[[^\]]+\])*\s*\?>/; close ARGV if eof' | grep .; then
  echo "::error::Escape output with e()"; fail=1
fi

# 4. Superglobals read directly in views.
if grep -rnE '\$_(GET|POST|REQUEST|COOKIE|SERVER)\b' app/views --include='*.php'; then
  echo "::error::Views must not read superglobals"; fail=1
fi

exit $fail
