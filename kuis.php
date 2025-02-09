<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuis Tingkat Introvert - MY INTROVERT</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/all.css" rel="stylesheet">
    <style>
        body {
            background-color:rgb(166, 198, 247);
        }
        .quiz-container {
            max-width: 800px;
            margin: 2rem auto;
            background-color:rgb(153, 221, 184);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .progress {
            height: 10px;
            margin-bottom: 2rem;
        }
        .btn-option {
            width: 100%;
            text-align: left;
            margin-bottom: 0.5rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        .btn-option:hover, .btn-option.active {
            background-color: #e9ecef;
            border-color: #0d6efd;
        }
        .btn-option.active {
            font-weight: bold;
        }
        .result-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        .hide {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container quiz-container">
        <h1 class="text-center mb-4">Kuis Tingkat Introvert</h1>
        <div class="progress">
            <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div id="quiz">
            <h5 id="question" class="mb-4"></h5>
            <div id="options" class="mb-4"></div>
            <button id="submit" class="btn btn-primary float-end">Selanjutnya</button>
        </div>
        <div id="result" class="mt-4 text-center hide">
            <i id="result-icon" class="fas fa-user result-icon"></i>
            <h2>Hasil Kuis</h2>
            <p id="score" class="fs-4"></p>
            <p id="interpretation" class="fs-5"></p>
            <button id="nextPage" class="btn btn-success mt-3" onclick="goToNextPage()">Lanjut ke Halaman Berikutnya</button>
        </div>
    </div>

    <script>
        const quizData = [
            {
                question: "Bagaimana perasaan Anda ketika harus berbicara di depan umum?",
                options: ["Sangat cemas dan ingin menghindarinya", "Cukup gugup tetapi bisa mengatasi", "Sedikit gugup tetapi bisa menanganinya dengan baik", "Merasa nyaman dan percaya diri"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Seberapa sering Anda merasa kewalahan setelah berinteraksi sosial yang lama?",
                options: ["Hampir selalu", "Sering", "Kadang-kadang", "Jarang atau tidak pernah"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Bagaimana Anda biasanya menghabiskan waktu luang?",
                options: ["Sendirian dengan hobi pribadi", "Dengan satu atau dua teman dekat", "Dalam kelompok kecil", "Dalam acara sosial besar"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Seberapa nyaman Anda memulai percakapan dengan orang asing?",
                options: ["Sangat tidak nyaman", "Agak tidak nyaman", "Cukup nyaman", "Sangat nyaman"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Bagaimana Anda menanggapi konflik interpersonal?",
                options: ["Menghindari konflik sama sekali", "Mencoba menyelesaikan secara tidak langsung", "Menghadapi dengan hati-hati", "Langsung menghadapi dan menyelesaikan"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Seberapa mudah bagi Anda untuk mengekspresikan perasaan Anda kepada orang lain?",
                options: ["Sangat sulit", "Cukup sulit", "Cukup mudah", "Sangat mudah"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Bagaimana Anda merasa tentang bekerja dalam tim?",
                options: ["Sangat tidak nyaman", "Sedikit tidak nyaman", "Cukup nyaman", "Sangat nyaman dan produktif"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Seberapa sering Anda merasa perlu 'mengisi ulang baterai' setelah interaksi sosial?",
                options: ["Setelah hampir setiap interaksi", "Setelah interaksi yang panjang atau intens", "Kadang-kadang", "Jarang atau tidak pernah"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Bagaimana Anda merespon ketika seseorang mengkritik Anda?",
                options: ["Sangat terganggu dan defensif", "Cukup terganggu tapi mencoba menerimanya", "Sedikit terganggu tapi bisa menghadapinya", "Menerimanya dengan baik dan melihatnya sebagai kesempatan untuk berkembang"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Seberapa nyaman Anda dalam situasi sosial yang tidak terstruktur (misalnya, pesta)?",
                options: ["Sangat tidak nyaman", "Cukup tidak nyaman", "Cukup nyaman", "Sangat nyaman"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Bagaimana Anda biasanya merespon ketika seseorang meminta bantuan Anda?",
                options: ["Mencoba menghindari atau menolak", "Membantu dengan enggan", "Membantu dengan senang hati", "Aktif mencari cara untuk membantu"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Seberapa mudah bagi Anda untuk membuat kesan pertama yang baik?",
                options: ["Sangat sulit", "Cukup sulit", "Cukup mudah", "Sangat mudah"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Bagaimana Anda merasa tentang berbagi pengalaman pribadi dengan orang lain?",
                options: ["Sangat tidak nyaman dan menghindarinya", "Tidak nyaman tapi kadang melakukannya", "Cukup nyaman dalam situasi tertentu", "Sangat nyaman dan sering melakukannya"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Seberapa baik Anda dalam membaca isyarat non-verbal dari orang lain?",
                options: ["Sangat buruk", "Cukup buruk", "Cukup baik", "Sangat baik"],
                scores: [3, 2, 1, 0]
            },
            {
                question: "Bagaimana Anda menangani situasi di mana Anda harus berimprovisasi dalam interaksi sosial?",
                options: ["Sangat stres dan ingin menghindarinya", "Cukup stres tapi mencoba mengatasinya", "Sedikit gugup tapi bisa menanganinya", "Merasa nyaman dan menikmati tantangannya"],
                scores: [3, 2, 1, 0]
            }
        ];

        let currentQuestion = 0;
        let score = 0;

        const questionEl = document.getElementById("question");
        const optionsEl = document.getElementById("options");
        const submitBtn = document.getElementById("submit");
        const quizEl = document.getElementById("quiz");
        const resultEl = document.getElementById("result");
        const scoreEl = document.getElementById("score");
        const interpretationEl = document.getElementById("interpretation");
        const progressBar = document.getElementById("progress-bar");
        const resultIcon = document.getElementById("result-icon");

        function loadQuestion() {
            const question = quizData[currentQuestion];
            questionEl.textContent = `${currentQuestion + 1}. ${question.question}`;

            optionsEl.innerHTML = "";
            question.options.forEach((option, index) => {
                const button = document.createElement("button");
                button.textContent = option;
                button.classList.add("btn", "btn-option", "mb-2");
                button.onclick = () => selectOption(index);
                optionsEl.appendChild(button);
            });

            updateProgressBar();
        }

        function selectOption(index) {
            const buttons = optionsEl.getElementsByTagName("button");
            for (let button of buttons) {
                button.classList.remove("active");
            }
            buttons[index].classList.add("active");
        }

        function updateProgressBar() {
            const progress = ((currentQuestion + 1) / quizData.length) * 100;
            progressBar.style.width = `${progress}%`;
            progressBar.setAttribute("aria-valuenow", progress);
        }

        function showResult() {
            quizEl.classList.add("hide");
            resultEl.classList.remove("hide");

            let level;
            const maxScore = quizData.length * 3;
            const percentage = (score / maxScore) * 100;

            if (percentage <= 33) {
                level = "Basic Introvert";
                resultIcon.className = "fas fa-smile result-icon text-success";
            } else if (percentage <= 66) {
                level = "Intermediate Introvert";
                resultIcon.className = "fas fa-meh result-icon text-warning";
            } else {
                level = "Advanced Introvert";
                resultIcon.className = "fas fa-frown result-icon text-danger";
            }

            scoreEl.textContent = `Skor Anda: ${score} dari ${maxScore} (${percentage.toFixed(2)}%)`;
            interpretationEl.textContent = getRecommendation(level);
        }

        function getRecommendation(level) {
            switch(level) {
                case "Basic Introvert":
                    return "Anda mungkin merasa nyaman dalam situasi sosial tertentu...";
                case "Intermediate Introvert":
                    return "Anda mungkin mengalami tantangan dalam situasi sosial...";
                case "Advanced Introvert":
                    return "Anda mungkin merasa sangat kewalahan dalam situasi sosial...";
            }
        }

        function goToNextPage() {
            window.location.href = "index.php";
        }

        submitBtn.onclick = () => {
            const selectedButton = optionsEl.querySelector(".active");
            if (!selectedButton) {
                alert("Pilih salah satu opsi sebelum melanjutkan!");
                return;
            }
            const selectedIndex = [...optionsEl.children].indexOf(selectedButton);
            score += quizData[currentQuestion].scores[selectedIndex];
            currentQuestion++;
            if (currentQuestion < quizData.length) {
                loadQuestion();
            } else {
                showResult();
            }
        };

        loadQuestion();
    </script>
</body>
</html>