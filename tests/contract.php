<?php

function fail_test($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

if (($argv[1] ?? null) !== null) {
    require_once __DIR__ . '/../api/helper.php';

    if ($argv[1] === 'list') {
        output_json([["id" => "example", "title" => "Example", "url" => "https://www.myinstants.com/en/instant/example", "mp3" => "https://www.myinstants.com/media/sounds/example.mp3"]], "200", [
            "page" => 2,
            "count" => 1,
            "total_pages" => 3,
            "has_next" => true
        ]);
    }

    if ($argv[1] === 'detail') {
        output_json(["id" => "example", "title" => "Example"], "200");
    }

    if ($argv[1] === 'error') {
        output_error("Query parameter 'id' is required", "400");
    }

    fail_test("Unknown test case");
}

function capture_response($case) {
    $pipes = [];
    $process = proc_open([PHP_BINARY, __FILE__, $case], [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ], $pipes);

    if (!is_resource($process)) {
        fail_test("Unable to start response contract test process");
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        fail_test("Response helper failed: " . $stderr);
    }

    $response = json_decode($stdout, true);
    if (!is_array($response)) {
        fail_test("Response helper did not return valid JSON");
    }

    return $response;
}

function expect_keys($response, $keys, $case) {
    if (array_keys($response) !== $keys) {
        fail_test("Unexpected $case response keys: " . implode(", ", array_keys($response)));
    }
}

$list = capture_response("list");
expect_keys($list, ["status", "author", "page", "count", "total_pages", "has_next", "data"], "list");
if ($list["status"] !== "200" || $list["author"] !== "Djinask" || $list["page"] !== 2 ||
    $list["count"] !== 1 || $list["total_pages"] !== 3 || $list["has_next"] !== true ||
    !is_array($list["data"]) || $list["data"][0]["id"] !== "example") {
    fail_test("List response values do not match the existing API contract");
}

$detail = capture_response("detail");
expect_keys($detail, ["status", "author", "data"], "detail");
if ($detail["status"] !== "200" || $detail["author"] !== "Djinask" ||
    $detail["data"]["id"] !== "example") {
    fail_test("Detail response values do not match the existing API contract");
}

$error = capture_response("error");
expect_keys($error, ["status", "author", "message"], "error");
if ($error["status"] !== "400" || $error["author"] !== "Djinask" ||
    $error["message"] !== "Query parameter 'id' is required") {
    fail_test("Error response values do not match the existing API contract");
}

fwrite(STDOUT, "API response envelope contract tests passed" . PHP_EOL);
