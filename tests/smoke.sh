#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-http://127.0.0.1:8080}"

check_response() {
    local path="$1"
    local expected_status="$2"
    local expected_shape="$3"
    local response status body

    response="$(curl -sS --max-time 10 -w '\n%{http_code}' "$BASE_URL$path")"
    status="${response##*$'\n'}"
    body="${response%$'\n'*}"

    if [[ "$status" != "$expected_status" ]]; then
        printf 'Expected HTTP %s for %s, got %s\n' "$expected_status" "$path" "$status" >&2
        exit 1
    fi

    if ! jq -e "$expected_shape" <<<"$body" >/dev/null; then
        printf 'Unexpected JSON response for %s:\n%s\n' "$path" "$body" >&2
        exit 1
    fi
}

check_response "/" 200 '.status == "200" and .author == "abdipr" and (.message | type == "string")'
check_response "/trending" 404 '.status == "404" and .author == "abdipr" and (.message | type == "string")'
check_response "/best" 404 '.status == "404" and .author == "abdipr" and (.message | type == "string")'
check_response "/detail" 400 '.status == "400" and .author == "abdipr" and (.message | type == "string")'
check_response "/favorites" 400 '.status == "400" and .author == "abdipr" and (.message | type == "string")'
check_response "/uploaded" 400 '.status == "400" and .author == "abdipr" and (.message | type == "string")'
check_response "/search" 404 '.status == "404" and .author == "abdipr" and (.message | type == "string")'
check_response "/recent?page=invalid" 400 '.status == "400" and .author == "abdipr" and (.message | type == "string")'
check_response "/not-an-endpoint" 404 '.status == "404" and .author == "abdipr" and (.message | type == "string")'

printf 'API routing and HTTP smoke tests passed\n'
