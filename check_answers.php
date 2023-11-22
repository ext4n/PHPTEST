<?php
$defaultTitle = 'Тест';
if (isset($_POST['quiz_file'])) {
    $quiz_file = 'qust/' . basename($_POST['quiz_file']) . '.json'; // Получаем файл вопросов

    if (file_exists($quiz_file)) {
        $json_data = file_get_contents($quiz_file);
        $data = json_decode($json_data, true);
?>
<!DOCTYPE html>
<html>
<head>
    <?php if (!empty($data) && isset($data['name']) && !empty($data['name'])): ?>
        <title>Ответы на опрос «<?= htmlspecialchars($data['name']) ?>»</title>
    <?php else: ?>
        <title><?= $defaultTitle ?></title>
    <?php endif; ?>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
   <link href="scripts/styles/styles.css" rel="stylesheet" type="text/css">
    <link rel="shortcut icon" href="" type="image/x-icon" />
    <link rel="apple-touch-icon" href="" />
</head>
<body>
    <?php
    $name = $_POST['name'] ?? '';
    ?>
    <h1>
        <?php if (!empty($data) && isset($data['name']) && !empty($data['name'])): ?>
            <?= htmlspecialchars($data['name']) ?>
        <?php else: ?>
            <?= $defaultTitle ?>
        <?php endif; ?>
    </h1>

    <p>Ваше имя и фамилия: <?= htmlspecialchars($name) ?></p>
    <p>Дата и время заполнения теста: <?= date('d.m.Y H:i:s') ?></p>

    <p id="totalCorrect">Правильных ответов: <span class="loading"></span></p>

    <?php
    $questions = $data['questions'];
    $total_questions = count($questions);
    $score = 0;
    ?>

    <table>
        <tr>
            <th>#</th>
            <th>Вопрос</th>
            <th>Ответ</th>
        </tr>
        <?php foreach ($questions as $index => $question): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><strong><?= $question['description'] ?></strong></td>

                <?php if (isset($_POST[$question['id']])): ?>
                    <?php
                    $user_answer = $_POST[$question['id']];
                    $correct_answer = $question['correct_answer'];
                    ?>
                    <?php if ($user_answer === $correct_answer): ?>
                        <td style="color:#0f9d58;"><strong>Ответ верный:</strong> <?= $question['answers'][$user_answer] ?></td>
                        <?php $score++; ?>
                    <?php else: ?>
                        <td style="color:#e34234;"><strong>Ответ «<?= $question['answers'][$user_answer] ?>» неправильный. <br><br> Правильный ответ: <br><?= $question['answers'][$correct_answer] ?></strong></td>
                    <?php endif; ?>
                <?php else: ?>
                    <td>-</td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>

</body>
    <script>
        window.onload = function() {
            var score = <?= $score ?>;
            var totalQuestions = <?= $total_questions ?>;
            var percentage = (score / totalQuestions) * 100;

            var totalCorrect = document.getElementById('totalCorrect');
            totalCorrect.innerHTML = 'Правильных ответов: ' + score + ' / ' + totalQuestions + ' (' + percentage.toFixed(2) + '%)';
        };
    </script>
</html>



<?php
$quizFileName = basename($_POST['quiz_file']);

// Создаем или открываем файл для записи
$resultFileName = 'results_' . $quizFileName . '.html';
$resultFilePath = 'results/' . $resultFileName;
$resultFile = fopen($resultFilePath, 'a+');

if ($resultFile) {
    if (filesize($resultFilePath) === 0) { // Если файл пустой, добавляем заголовки
        $header = '<html><head><title>Результаты опроса</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body>';
        $header .= '<h2>' . htmlspecialchars($data['name']) . '</h2>'; // Вывод названия теста
        $header .= '<style>';
        $header .= 'table { border-collapse: collapse; width: 100%; }';
        $header .= 'th, td { padding: 5px; text-align: left; font-size: 12px; }';
        $header .= 'th { background-color: #f2f2f2; }';
        $header .= 'td { border-bottom: 1px solid #ddd; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }';
        $header .= '</style>';
        $header .= '<table><tr><th>Имя и фамилия</th><th>Дата</th>';

        // Добавляем заголовки вопросов
        foreach ($questions as $question) {
            $header .= '<th>' . htmlspecialchars($question['description']) . '</th>';
        }
        $header .= '<th>Правильных ответов</th></tr>';
        fwrite($resultFile, $header);
    }

    // Формируем строку с ответами
    $rowData = '<tr><td>' . htmlspecialchars($name) . '</td><td>' . date('d.m.Y H:i:s') . '</td>';
    $correctAnswers = 0;

    foreach ($questions as $question) {
        $rowData .= '<td>';
        if (isset($_POST[$question['id']])) {
            $userAnswer = $_POST[$question['id']];
            $correctAnswer = $question['correct_answer'];

            if ($userAnswer === $correctAnswer) {
                $correctAnswers++;
                $rowData .= '<span style="color:#0f9d58;"><strong>Ответ верный:</strong> ' . htmlspecialchars($question['answers'][$userAnswer]) . '</span>';
            } else {
                $rowData .= '<span style="color:#e34234;"><strong>Ответ «' . htmlspecialchars($question['answers'][$userAnswer]) . '» неправильный. <br><br> Правильный ответ: <br>' . htmlspecialchars($question['answers'][$correctAnswer]) . '</strong></span>';
            }
        } else {
            $rowData .= '-';
        }
        $rowData .= '</td>';
    }

    // Считаем процент правильных ответов
    $percentage = ($correctAnswers / count($questions)) * 100;
    $totalCorrectInfo = $correctAnswers . ' / ' . count($questions) . ' (' . number_format($percentage, 2) . '%)';
    $rowData .= '<td>' . $totalCorrectInfo . '</td></tr>';

    fwrite($resultFile, $rowData);

    fclose($resultFile);
    //echo 'Результаты обновлены в файле: ' . $resultFilePath;
} else {
   // echo 'Ошибка обновления результатов.';
}
?>






<?php
    } else {
        echo "Файл с вопросами не найден.";
    }
} else {
    echo "Недостаточно данных для проверки ответов.";
}
?>

