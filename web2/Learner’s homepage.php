<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learner's Homepage</title>
    <link rel="stylesheet" href="Learn&QF.css">
    <link rel="stylesheet" href="style.css">
</head>
<body onload="start()">
    <header>
        <nav>
            <ul>
                <li><a href="index.php"><img src="images/mindly.png" alt="Mindly Logo" /></a></li>

            </ul>
        </nav>
    </header>

    <section>
        <div class="topHeader">
            <span class="welcome" style="color: #a654e6">
                Welcome <span style="color: #5945ec">Sarah Khalid</span>
            </span>
        </div>
        <div style="display: flex; justify-content: flex-end;">
            <a href="index.php" style=" text-decoration: underline;">log-out</a>
        </div>
    </section>

    <section>
        <div class="LearnerInfo">
            <div class="LearnerDetails">
                <p>Name: Sarah Khalid</p>
                <p>Email: sarah.Khalid@gmail.com</p>
            </div>
            <div class="LearnerImg">
                <img
                    style="border: 0.3px; height: 100px; object-fit: contain"
                    src="images/sarah.jpg"
                    alt="profile picture"
                />
            </div>
        </div>
    </section>

    <main>
        <div class="quiz-section">
            <div class="quiz-header">
                <div></div>
                <div class="filter-controls">
                    <select class="filter-dropdown">
                        <option value="">...</option>
                        <option value="math">Mathematics</option>
                        <option value="web-dev">Web Development</option>
                    </select>
                    <button class="filter-btn">Filter</button>
                </div>
            </div>
        </div>

        <table>
            <caption>All Available Quizzes</caption>
            <thead>
                <tr>
                    <th>Topic</th>
                    <th>Educator</th>
                    <th>Number of Questions</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="Quiz score and feedback.html">Mathematics</a></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px; justify-content: center;">
                            <span>Mohammed Ali</span>
                            <img
                                style="border-radius: 50%; height: 40px; width: 40px; object-fit: cover;"
                                src="images/edu.png"
                                alt="educator picture"
                            />
                        </div>
                    </td>
                    <td>5</td>
                    <td><button class="take-quiz-btn">Take Quiz</button></td>
                </tr>
                <tr>
                    <td><a href="Quiz score and feedback.html">Web Development</a></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px; justify-content: center;">
                            <span>Omar Riyadh</span>
                            <img
                                style="border-radius: 50%; height: 40px; width: 40px; object-fit: cover;"
                                src="images/Omar.jpg"
                                alt="educator picture"
                            />
                        </div>
                    </td>
                    <td>25</td>
                    <td><button class="take-quiz-btn">Take Quiz</button></td>
                </tr>
                <tr>
                    <td>...</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <table class="recommendation-table">
            <caption>Recommended Questions</caption>
            
            
            <thead>
                <tr>
                    <th>Topic</th>
                    <th>Educator</th>
                    <th>Question</th>
                    <th>Status</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Mathematics</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px; justify-content: center;">
                            <span>Mohammed Ali</span>
                            <img
                                style="border-radius: 50%; height: 40px; width: 40px; object-fit: cover;"
                                src="images/edu.png"
                                alt="educator picture"
                            />
                        </div>
                    </td>
                    <td>
                        <img
                            style="height: 100px; object-fit: contain; margin-left: 20svh; box-shadow: 0px 3px 17px rgb(61, 61, 61);"
                            src="images/calc.jpg"
                            alt="question picture"
                        /><br /> <br />

                        Solve for x:
3x+7=25?<br>
                        <ol style="list-style: circle">
                            <li>5</li>
                            <li>4</li>
                            <li style="background-color: lightgreen">6</li>
                            <li>8</li>
                        </ol>
                    </td>
                    <td>Approved</td>
                    <td>Great Question!</td>
                </tr>
                <tr>
                    <td>Web Development</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px; justify-content: center;">
                            <span>Omar Riyadh</span>
                            <img
                                style="border-radius: 50%; height: 40px; width: 40px; object-fit: cover;"
                                src="images/Omar.jpg"
                                alt="educator picture"
                            />
                        </div>
                    </td>
                    <td>
                         <img
                            style="height: 100px; object-fit: contain; margin-left: 20svh; box-shadow: 0px 3px 17px rgb(61, 61, 61);"
                            src="images/JS.png"
                            alt="question picture"
                        /><br /> <br />
                        What is the correct way to declare a variable in JavaScript?<br>
                        <ol style="list-style: circle">
                            <li>variable x = 5;</li>
                            <li>int x = 5;</li>
                            <li style="background-color: lightgreen">let x = 5;</li>
                            <li>x := 5;</li>
                        </ol>
                    </td>
                    <td>Pending Review</td>
                    <td>Waiting for approval</td>
                </tr>
                <tr>
                    <td>...</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        
        <div style="text-align: center; margin: 30px 0;">
            <form action="recommended.php"> 
            <button class="recommend-btn" type="submit">Recommend a Question</button>
            </form>
        </div>
    </main>
    
    <br />
    <div class="footer-container">
        <footer>
            <p>&copy; 2025 Mindly. All rights reserved.</p>
        </footer>
    </div>
    
    <script>
    function start() {
        document.body.style.opacity = "1";
        
        // Typing animation with cursor
        const el = document.querySelector(".welcome");
        const text = "Welcome Sarah Khalid";
        const textLength = text.length;
        
        // Clear existing content and set initial state
        el.textContent = "";
        el.style.borderRight = "1px solid #000";
        el.style.width = "0";
        
        // Create dynamic CSS keyframes
        const style = document.createElement("style");
        style.innerHTML = `
            @keyframes typing {
                from { width: 0; }
                to { width: ${textLength}ch; }
            }
            
            @keyframes blinkCursor {
                50% { border-color: transparent; }
            }
            
            .welcome {
                animation: typing 2s steps(${textLength}) 1s 1 forwards, 
                           blinkCursor 0.75s step-end infinite;
                white-space: nowrap;
                overflow: hidden;
            }
        `;
        document.head.appendChild(style);
        
        // Set the text content after a delay to match animation
        setTimeout(() => {
            el.textContent = text;
        }, 1000);
        
        // Remove cursor after animation ends
        el.addEventListener("animationend", () => {
            el.style.borderRight = "none";
        });
    }
    
    // Handle take quiz buttons
    const takeQuizBtns = document.querySelectorAll('.take-quiz-btn');
    takeQuizBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            window.location.href = 'TakeAquiz.php';
        });
    });
    
    // Handle filter button
    const filterBtn = document.querySelector('.filter-btn');
    if (filterBtn) {
        filterBtn.addEventListener('click', function() {
            alert('Filter functionality coming soon!');
        });
    }
</script>
</body>
</html>
