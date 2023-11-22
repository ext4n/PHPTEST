<?php
$data = null;

if (isset($_GET['q'])) {
    $quiz_file = 'qust/' . basename($_GET['q']) . '.json';
    if (file_exists($quiz_file)) {
        $json_data = file_get_contents($quiz_file);
        $data = json_decode($json_data, true);
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>
        <?php
        $defaultTitle = 'Тест';
        if (!empty($data) && isset($data['name']) && !empty($data['name'])) {
            echo 'Опрос «' . htmlspecialchars($data['name']) . '»';
        } else {
            echo $defaultTitle;
        }
        ?>
    </title>
 <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
<link href="scripts/styles/styles.css" rel="stylesheet" type="text/css">
    <link rel="shortcut icon" href="" type="image/x-icon" />
    <link rel="apple-touch-icon" href="" />
</head>
<body>
<h1 class="main">
    <?php
    if (!empty($data) && isset($data['name']) && !empty($data['name'])) {
        echo 'Опрос на тему: <br><br> «' . htmlspecialchars($data['name']) . '»';
    } else {
        echo $defaultTitle;
    }
    ?>
</h1>

    <?php
    if (!empty($data)) {
        if (!empty($data['password'])) {
            if (isset($_GET['ps'])) {
                $password = $_GET['ps'];
                if ($data['password'] === $password) {
                    displayQuestions($data);
                } else {
                    echo 'Пароль неверный, попробуйте снова.';
                    displayPasswordForm($_GET['q']);
                }
            } else {
                displayPasswordForm($_GET['q']);
            }
        } else {
            displayQuestions($data);
        }
    } else {
        echo '<p>Извините, вопросы не найдены.</p>';
    }

function displayQuestions($data) {
    echo '<form method="POST" action="check_answers.php">';
    echo '<input type="hidden" name="quiz_file" value="' . htmlspecialchars($_GET['q']) . '">'; 
    echo '<label for="name"><span class="circle">0</span>Ваше имя и фамилия:</label>';
    echo '<input type="text" id="name" name="name" required><br><br>';

    $questions = $data['questions'];
    $questionNumber = 1;

foreach ($questions as $question) {
    echo '<fieldset class="' . ($questionNumber % 2 === 0 ? 'animate-left' : 'animate-right') . '">';

    // Проверяем наличие значения image и выводим, если есть
    if (isset($question['image']) && !empty($question['image'])) {
        echo '<img src="' . htmlspecialchars($question['image']) . '" alt="Question Image">';
    }

    echo '<legend style="border: 3px solid ' . (isset($question['color']) && !empty($question['color']) ? htmlspecialchars($question['color']) : '#12429c') . '">';
    echo '<span class="circle">' . $questionNumber . '</span>' . $question['description'] . '</legend>';

    // Выводим ответы на вопросы
    foreach ($question['answers'] as $answerKey => $answerText) {
        echo '<label>';
        echo '<input type="radio" name="' . $question['id'] . '" value="' . $answerKey . '" required>';
        echo $answerText;
        echo '</label><br>';
    }

    echo '</fieldset>';
    $questionNumber++;
}

    echo '<div class="frame"><input class="custom-btn btn-11" type="submit" value="Отправить"><div class="dot"></div></div>';
    echo '</form>';
}
    function displayPasswordForm($questionFile) {
        echo '<form method="GET" action="">';
        echo '<input type="hidden" name="q" value="' . htmlspecialchars($questionFile) . '">';
        echo '<label for="user_password">Введите пароль:</label>';
        echo '<input type="password" name="ps" id="user_password" autocomplete="new-password" required>';
        echo '<input class="custom-btn btn-11" type="submit" value="Открыть опрос">';
        echo '</form>';
    }
    ?>
<script>
const fieldsets = document.querySelectorAll('fieldset');

fieldsets.forEach((fieldset) => {
    const radioButtons = fieldset.querySelectorAll('input[type="radio"]');
    
    radioButtons.forEach((radioButton) => {
        radioButton.addEventListener('change', function(event) {
            const labels = fieldset.querySelectorAll('.bold-label');
            labels.forEach(label => {
                label.classList.remove('bold-label');
            });

            const label = this.closest('label');
            if (label) {
                label.classList.add('bold-label');
            }
        });
    });
});

window.addEventListener('load', () => {
    window.scrollBy(0, 50); // Добавляем небольшой скролл при полной загрузке
});

window.addEventListener('scroll', () => {
    const fieldsets = document.querySelectorAll('fieldset');
    fieldsets.forEach((fieldset, index) => {
        if (isElementInViewport(fieldset)) {
            fieldset.classList.add(index % 2 === 0 ? 'animate-left' : 'animate-right', 'animate');
        }
    });
});


function isElementInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}



window.addEventListener('load', function() {
    var jsonData = <?php echo !empty($data) ? json_encode($data) : "{}"; ?>;
    if (Object.keys(jsonData).length !== 0) {
        var legend = document.querySelectorAll('legend');
        var labelForName = document.querySelector('label[for="name"]');
        var fieldsets = document.querySelectorAll('fieldset');

        // Применяем цвета для legend, label и fieldset
        if (jsonData.color) {
            legend.forEach(function(legendItem) {
                legendItem.style.borderColor = jsonData.color;
            });
            labelForName.style.borderColor = jsonData.color;
            fieldsets.forEach(function(fieldsetItem) {
                fieldsetItem.style.borderColor = jsonData.color;
            });
        }

        if (jsonData.image) {
            var nameField = document.getElementById('name').parentNode;
            var image = new Image();
            image.src = jsonData.image;
            image.style.width = '100%'; 
            image.style.borderRadius = '10px';
            image.style.boxShadow = '0px 4px 8px rgba(0, 0, 0, 0.2)'; // Тень
            image.style.border = '2px solid #333'; 
            image.style.marginBottom = '20px';

            // Анимация появления изображения
            image.style.opacity = '0';
            image.style.transition = 'opacity 1s ease-in-out';
            setTimeout(function() {
                image.style.opacity = '1';
            }, 500);

            nameField.insertBefore(image, nameField.firstChild);
        }
    }
});

let startTime;

document.addEventListener('DOMContentLoaded', function() {
    const clockElement = document.getElementById('clock');
    const elapsedTimeElement = document.getElementById('elapsed-time');
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    
    // Запоминаем время начала опроса при выборе первого ответа
    radioButtons.forEach(radioButton => {
        radioButton.addEventListener('change', function(event) {
            if (!startTime) {
                startTime = new Date();
                updateElapsedTime();
                setInterval(updateElapsedTime, 1000);
            }
        });
    });

    // Функция для обновления затраченного времени
    function updateElapsedTime() {
        const currentTime = new Date();
        const elapsedTime = (currentTime - startTime) / 1000; // Получаем затраченное время в секундах
        const formattedTime = `${Math.floor(elapsedTime / 60)}:${Math.floor(elapsedTime % 60)}`; // Преобразуем в формат ММ:СС
        elapsedTimeElement.textContent = `Затраченное время: ${formattedTime}`;
    }

    // Обновление текущего времени каждую секунду
    setInterval(() => {
        const now = new Date();
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');
        clockElement.textContent = `${hours}:${minutes}:${seconds}`;
    }, 1000);
});

</script>
<div id="clock" class="clock-container">
  <span class="loading"></span>
  <div id="elapsed-time"></div>
</div>
</body>
</html>
