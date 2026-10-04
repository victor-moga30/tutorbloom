<?php
require_once __DIR__ . '/../includes/init.php';
require_role('student');
require_once __DIR__ . '/../includes/ajax_lab_helpers.php';

header('Content-Type: application/xml; charset=utf-8');

try {
    $filters = lab_read_filters_from_get();
    $offset = lab_get_offset();
    $limit = lab_get_limit();

    $allTutors = lab_get_filtered_tutors($pdo, $filters);
    $total = count($allTutors);
    $pageTutors = lab_prepare_tutors_for_output(array_slice($allTutors, $offset, $limit));

    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;

    $root = $xml->createElement('response');
    $root->setAttribute('success', 'true');
    $root->setAttribute('total', (string)$total);
    $root->setAttribute('offset', (string)$offset);
    $root->setAttribute('limit', (string)$limit);
    $xml->appendChild($root);

    $tutorsNode = $xml->createElement('tutors');
    $root->appendChild($tutorsNode);

    foreach ($pageTutors as $tutor) {
        $tutorNode = $xml->createElement('tutor');
        $tutorsNode->appendChild($tutorNode);

        foreach ($tutor as $key => $value) {
            if ($key === 'match_reasons') {
                $reasonsNode = $xml->createElement('match_reasons');

                foreach ($value as $reason) {
                    $reasonNode = $xml->createElement('reason');
                    $reasonNode->appendChild($xml->createTextNode($reason));
                    $reasonsNode->appendChild($reasonNode);
                }

                $tutorNode->appendChild($reasonsNode);
            } else {
                $child = $xml->createElement($key);
                $child->appendChild($xml->createTextNode((string)$value));
                $tutorNode->appendChild($child);
            }
        }
    }

    echo $xml->saveXML();
} catch (Exception $exception) {
    http_response_code(500);

    $xml = new DOMDocument('1.0', 'UTF-8');
    $root = $xml->createElement('response');
    $root->setAttribute('success', 'false');
    $xml->appendChild($root);

    $message = $xml->createElement('message');
    $message->appendChild($xml->createTextNode('Nu s-au putut incarca tutorii.'));
    $root->appendChild($message);

    echo $xml->saveXML();
}