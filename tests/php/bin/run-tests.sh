#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../../.." && pwd)"
DOCKER=(docker)
if ! docker info >/dev/null 2>&1; then
  DOCKER=(sudo docker)
fi
PHP_IMAGE='php@sha256:6ce95208609dc66df163ab936c970b3b34cd901b85c747102c5999f08ade9143'
PHPUNIT_DIR='/tmp/ps_faviconnotificationbo-phpunit'
PHPUNIT_PHAR="${PHPUNIT_DIR}/phpunit-5.7.27.phar"
PHPUNIT_ASC="${PHPUNIT_DIR}/phpunit-5.7.27.phar.asc"
PHPUNIT_SHA256='f0300a13f2653bee10b0ce24dbc8d94e65dc9be8966d822b9e455b6b294be16e'
GPG_FPR='D8406D0D82947747293778314AA394086372C20A'
EXPECTED_TEST_COUNT=5

mkdir -p "${PHPUNIT_DIR}"

if [[ ! -f "${PHPUNIT_PHAR}" ]] || [[ "$(sha256sum "${PHPUNIT_PHAR}" | awk '{print $1}')" != "${PHPUNIT_SHA256}" ]]; then
  curl -fsSL -o "${PHPUNIT_PHAR}" 'https://phar.phpunit.de/phpunit-5.7.27.phar'
  curl -fsSL -o "${PHPUNIT_ASC}" 'https://phar.phpunit.de/phpunit-5.7.27.phar.asc'
fi

ACTUAL_SHA256="$(sha256sum "${PHPUNIT_PHAR}" | awk '{print $1}')"
if [[ "${ACTUAL_SHA256}" != "${PHPUNIT_SHA256}" ]]; then
  echo "PHPUnit PHAR checksum mismatch: ${ACTUAL_SHA256}" >&2
  exit 1
fi

export GNUPGHOME="$(mktemp -d)"
chmod 700 "${GNUPGHOME}"
gpg --batch --keyserver hkps://keys.openpgp.org --recv-keys "${GPG_FPR}"
set +e
GPG_STATUS="$(gpg --batch --status-fd 1 --verify "${PHPUNIT_ASC}" "${PHPUNIT_PHAR}" 2>/dev/null)"
set -e
if ! grep -q "\[GNUPG:\] VALIDSIG ${GPG_FPR} " <<<"${GPG_STATUS}"; then
  echo "PHPUnit PHAR signature verification failed" >&2
  echo "${GPG_STATUS}" >&2
  rm -rf "${GNUPGHOME}"
  exit 1
fi
rm -rf "${GNUPGHOME}"

mapfile -t DISCOVERED < <(find "${ROOT}/tests/php/Unit" -name '*Test.php' | sort)
EXPECTED_FILES=(
  "${ROOT}/tests/php/Unit/AdminConfigureFaviconBoControllerTest.php"
  "${ROOT}/tests/php/Unit/DirectAccessGuardTest.php"
  "${ROOT}/tests/php/Unit/FaviconNotificationModuleTest.php"
  "${ROOT}/tests/php/Unit/TemplateContractTest.php"
  "${ROOT}/tests/php/Unit/UpgradeModule210Test.php"
)

if [[ "${#DISCOVERED[@]}" -ne "${EXPECTED_TEST_COUNT}" ]]; then
  echo "Unexpected test file count: ${#DISCOVERED[@]}" >&2
  exit 1
fi

for i in "${!EXPECTED_FILES[@]}"; do
  if [[ "${DISCOVERED[$i]}" != "${EXPECTED_FILES[$i]}" ]]; then
    echo "Test manifest mismatch at index ${i}" >&2
    printf ' expected: %s\n actual:   %s\n' "${EXPECTED_FILES[$i]}" "${DISCOVERED[$i]}" >&2
    exit 1
  fi
done

"${DOCKER[@]}" run --rm \
  -v "${ROOT}:/src" \
  -v "${PHPUNIT_DIR}:/phpunit:ro" \
  -w /src \
  "${PHP_IMAGE}" \
  php -r 'if (PHP_VERSION !== "5.6.40") { fwrite(STDERR, "Unexpected PHP version: ".PHP_VERSION.PHP_EOL); exit(1);}'

SHUFFLE_ORDER="$( "${DOCKER[@]}" run --rm "${PHP_IMAGE}" php -r '
$files = array(
  "AdminConfigureFaviconBoControllerTest.php",
  "DirectAccessGuardTest.php",
  "FaviconNotificationModuleTest.php",
  "TemplateContractTest.php",
  "UpgradeModule210Test.php",
);
sort($files);
srand(20261008);
shuffle($files);
echo implode("\n", $files);
')"

SHUFFLE_XML="$(mktemp)"
cat > "${SHUFFLE_XML}" <<EOF
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    bootstrap="/src/tests/php/bootstrap.php"
    colors="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    beStrictAboutTestsThatDoNotTestAnything="true"
    beStrictAboutOutputDuringTests="true"
>
    <testsuites>
        <testsuite name="module-tests-shuffled">
EOF

while IFS= read -r file; do
  echo "            <file>/src/tests/php/Unit/${file}</file>" >> "${SHUFFLE_XML}"
done <<< "${SHUFFLE_ORDER}"

cat >> "${SHUFFLE_XML}" <<'EOF'
        </testsuite>
    </testsuites>
</phpunit>
EOF

run_phpunit() {
  local config="$1"
  local extra_mount=()
  if [[ "${config}" != "/src/phpunit.xml.dist" ]]; then
    extra_mount=(-v "${SHUFFLE_XML}:/tmp/phpunit-shuffle.xml:ro")
    config="/tmp/phpunit-shuffle.xml"
  fi
  "${DOCKER[@]}" run --rm \
    -v "${ROOT}:/src" \
    -v "${PHPUNIT_DIR}:/phpunit:ro" \
    "${extra_mount[@]}" \
    -w /src \
    "${PHP_IMAGE}" \
    php /phpunit/phpunit-5.7.27.phar --configuration "${config}"
}

run_phpunit '/src/phpunit.xml.dist'
run_phpunit '/src/phpunit.xml.dist'
run_phpunit 'shuffled'
run_phpunit 'shuffled'

rm -f "${SHUFFLE_XML}"
