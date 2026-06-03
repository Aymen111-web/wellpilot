<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\WellnessAssessmentController;
use Illuminate\Http\Request;

$controller = new WellnessAssessmentController();

echo "--- Request 1: No nickname ---\n";
$req1 = Request::create('/assessments', 'GET');
$res1 = $controller->index($req1);
echo "Status: " . $res1->getStatusCode() . "\n";
echo "Count: " . count(json_decode($res1->getContent())) . "\n\n";

echo "--- Request 2: With nickname 'Tester' ---\n";
$req2 = Request::create('/assessments', 'GET', ['nickname' => 'Tester']);
$res2 = $controller->index($req2);
echo "Status: " . $res2->getStatusCode() . "\n";
$data2 = json_decode($res2->getContent());
echo "Count: " . count($data2) . "\n";
if (count($data2) > 0) {
    echo "First nickname: " . $data2[0]->nickname . "\n";
}

echo "\n--- Request 3: With nickname 'NonExistentUser' ---\n";
$req3 = Request::create('/assessments', 'GET', ['nickname' => 'NonExistentUser']);
$res3 = $controller->index($req3);
echo "Status: " . $res3->getStatusCode() . "\n";
echo "Count: " . count(json_decode($res3->getContent())) . "\n";
