<?php

$file_token = explode("\n", rtrim(file_get_contents("f.txt")));

for ($i = 0; $i < count($file_token); $i++) {
    $token = trim($file_token[$i]);
    $headers = explode("\n", "Host: api.marlboro.id\naccept: */*\naccept-language: en-US,en;q=0.9\ncontent-type: application/json\nsec-fetch-mode: cors\nsec-fetch-site: same-site\nuser-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.117 Safari/537.36\nmarlboro-token:$token");

    // Lanjutkan dengan pemanggilan API dan fungsi yang sudah ada
    $getdata = json_decode(request('https://api.marlboro.id/api/auth/get-profile', '{}', $headers, 'POST')[1], true);
    $emails = $getdata["data"]["email"];
    $spice_id = $getdata["data"]["spice_id"];
    $mobile_number = $getdata["data"]["mobile_number"];
    $tambah1 = request('https://api.marlboro.id/api/open_email_log/open_email_log', '{"utm":"https://www.marlboro.id/auth/memorabilia"}', $headers, 'POST')[1];
    $getchallenge = getchallenge($headers, $spice_id);

    for ($j = 0; $j < count($getchallenge); $j++) {
        echo "[" . ($j + 1) . "] Challenge " . ($j + 1) . "\n";
        $logging = logging($headers, $getchallenge[$j]);
    }

    // Tambahkan pemanggilan API baru untuk mendapatkan short profile setelah getchallenge selesai
    $shortProfile = getShortProfile($headers);
    $fullname = $shortProfile['data']['fullname'];
    $point = $shortProfile['data']['point'];

    // Tampilkan fullname dan point
    echo "Fullname: $fullname\n";
    echo "Point: $point\n";

    $simpan = fopen('Akun Marlboro 2024.txt', 'a');
}

function getchallenge($headers, $spiceid)
{
    $getallchallenge = json_decode(request('https://api.marlboro.id/api/challenge/get-all-challenge', '{"spice_id":' . $spiceid . ',"progress":"0"}', $headers, 'POST')[1], true);
    $arraychallenge = array();
    for ($i = 0; $i < count($getallchallenge['data']['challenge']); $i++) {
        $arraychallenge[$i][] = $getallchallenge['data']['challenge'][$i]['string_id'];
        $arraychallenge[$i][] = $getallchallenge['data']['challenge'][$i]['inspiration']['string_id'];
        $arraychallenge[$i][] = $getallchallenge['data']['challenge'][$i]['industri']['string_id'];
    }
    return $arraychallenge;
}

function getShortProfile($headers)
{
    return json_decode(request('https://api.marlboro.id/api/auth/get-short-profile', '{}', $headers, 'POST')[1], true);
}

function logging($headers, $arraychallenge)
{
    $arraytype = ["inspiration", "industri"];
    for ($i = 1; $i < count($arraychallenge); $i++) {
        echo "   [-] " . $arraychallenge[$i] . " = ";
        $arrayprogress = [0, 25, 50, 75, 100];
        for ($j = 0; $j < count($arrayprogress); $j++) {
            $logging = json_decode(request('https://api.marlboro.id/api/challenge/logging', '{"string_id":"' . $arraychallenge[$i] . '","type":"' . $arraytype[$i - 1] . '","progress":"' . $arrayprogress[$j] . '"}', $headers, 'POST')[1], true);
        }
        if (empty($logging['error'])) {
            echo "Sukses\n";
        } else {
            echo "Gagal\n";
            sleep(1);
            $i--;
        }
    }
    echo "   -> Claim 1000 Point = ";
    $claim = json_decode(request('https://api.marlboro.id/api/challenge/claim-point', '{"string_id":"' . $arraychallenge[2] . '"}', $headers, 'POST')[1], true);
    if (empty($claim['error'])) {
        echo "Sukses\n";
    } else {
        echo "Gagal\n";
    }
}

function request($url, $param, $headers = null, $request = 'POST', $cookie = null, $followlocation = 0, $proxy = null, $port = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($param != null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
    }
    if ($headers != null) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    if ($port != null) {
        curl_setopt($ch, CURLOPT_PORT, $port);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    } elseif ($port == null) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    if ($cookie != null) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    }
    if ($proxy != null) {
        $ip = "http://zproxy.lum-superproxy.io";
        $port = "22225";
        curl_setopt($ch, CURLOPT_PROXY, $ip);
        curl_setopt($ch, CURLOPT_PROXYPORT, $port);
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$proxy:yozok5sajm3w");
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    }
    if ($followlocation == 1) {
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 100);
    }
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execute = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($execute, 0, $header_size);
    $body = substr($execute, $header_size);
    curl_close($ch);
    return [$header, $body];
}
?>
