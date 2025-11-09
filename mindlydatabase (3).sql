-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 09, 2025 at 04:01 PM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mindlydatabase`
--

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `id` int(11) NOT NULL,
  `educatorID` int(11) NOT NULL,
  `topicID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`id`, `educatorID`, `topicID`) VALUES
(3341, 3344, 1111),
(3342, 3344, 3333),
(3343, 3345, 2222);

-- --------------------------------------------------------

--
-- Table structure for table `quizfeedback`
--

CREATE TABLE `quizfeedback` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comments` varchar(500) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `quizfeedback`
--

INSERT INTO `quizfeedback` (`id`, `quizID`, `rating`, `comments`, `date`) VALUES
(1, 3341, 5, 'Great quiz! very clear', '2025-11-08 19:53:38'),
(2, 3342, 4, 'Challenging but fun', '2025-11-08 19:53:52'),
(3, 3343, 3, 'Could use more questions', '2025-11-08 19:54:08');

-- --------------------------------------------------------

--
-- Table structure for table `quizquestion`
--

CREATE TABLE `quizquestion` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `question` varchar(500) NOT NULL,
  `questionFigureFileName` varchar(500) NOT NULL,
  `answerA` varchar(500) NOT NULL,
  `answerB` varchar(500) NOT NULL,
  `answerC` varchar(500) NOT NULL,
  `answerD` varchar(500) NOT NULL,
  `correctAnswer` enum('A','B','C','D') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `quizquestion`
--

INSERT INTO `quizquestion` (`id`, `quizID`, `question`, `questionFigureFileName`, `answerA`, `answerB`, `answerC`, `answerD`, `correctAnswer`) VALUES
(1, 3341, 'What does HTML stand for?', 'JS.png', 'Hyperlinks and Text Markup Language', 'Hyper Text Markup Language', 'Home Tool Markup LanguageHyper Transfer Markup Language', 'Hyper Transfer Markup Language', 'B'),
(3, 3343, ' What is 9 + 7?', 'En.jpg', '16', '10', '12', '14', 'A'),
(3334, 3341, 'What are main parts we use to build a website? (Structure, Style, Actions)', '', 'HTML, CSS, and JavaScript', ' HTML', ' CSS', 'JavaScript ', 'A'),
(3335, 3341, 'What does the \"W\" stand for in WWW?', '', 'World Wide Widget', 'World Wide Web', 'Wide Web Window', 'Web World Wide', 'B'),
(3336, 3341, 'We use different tags in HTML. What tag would you use to create a big, main title for your page?', '', '<big>', '<headline>', ' <h1>', '<main-title>', 'C'),
(3337, 3341, ' If you want to change the color of the text on your website what you will use', '', 'JavaScript', 'HTML', 'php', 'CSS', 'D'),
(3338, 3341, 'When a programmer finds and fixes a mistake in their code, what is that process called?', '', 'Compiling', 'Debugging', 'Executing', 'Refactoring', 'B'),
(3339, 3342, 'What is the opposite of the word \"happy\"?', '', 'Excited', 'Sad', 'Cheerful', 'Joyful', 'B'),
(3340, 3342, ' Which word tells you what someone or something is doing?', '', 'Verb', 'Adjective', 'Pronoun', 'Noun', 'A'),
(3341, 3342, 'The dog wagged ____ tail', '', 'him', 'they', 'the', 'its', 'D'),
(3342, 3342, 'How many vowel letters are in the alphabet?', 'english2.png', '7', '8', '5', '4', 'C'),
(3343, 3342, ' What sound does the letter \'C\' make in the word \"car\"?', 'english3.png', '/s/ (as in \"city\")', '/kʃ/ ', '/t/', '/k/', 'D'),
(3344, 3342, 'Which word is a noun in this sentence?\r\n\"The cat chased the mouse.\"', '', 'chased', 'the', 'cat', 'quickly', 'C');

-- --------------------------------------------------------

--
-- Table structure for table `recommendedquestion`
--

CREATE TABLE `recommendedquestion` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `learnerID` int(11) NOT NULL,
  `question` varchar(500) NOT NULL,
  `questionFigureFileName` varchar(500) NOT NULL,
  `answerA` varchar(500) NOT NULL,
  `answerB` varchar(500) NOT NULL,
  `answerC` varchar(500) NOT NULL,
  `answerD` varchar(500) NOT NULL,
  `correctAnswer` enum('A','B','C','D') NOT NULL,
  `status` enum('pending','approved','disapproved') NOT NULL,
  `comments` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `recommendedquestion`
--

INSERT INTO `recommendedquestion` (`id`, `quizID`, `learnerID`, `question`, `questionFigureFileName`, `answerA`, `answerB`, `answerC`, `answerD`, `correctAnswer`, `status`, `comments`) VALUES
(1, 3341, 3346, 'Which tag is used for creating a hyperlink in HTML?', 'JS.png', '<a>', '<link>', '<href>', '<src>', 'A', 'pending', ''),
(2, 3342, 3346, 'What is the value of x?\r\nx+5=9', 'calc.jpg', '5', '0', '4', '9', 'C', 'approved', 'good question!'),
(3, 3343, 3346, 'complete the sentence: \r\ni paid in _ cash', 'En.jpg', 'by', 'with', 'at ', 'in', 'D', 'disapproved', 'nice question but could be too difficult');

-- --------------------------------------------------------

--
-- Table structure for table `takenquiz`
--

CREATE TABLE `takenquiz` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `learnerID` int(11) NOT NULL,
  `score` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `takenquiz`
--

INSERT INTO `takenquiz` (`id`, `quizID`, `learnerID`, `score`) VALUES
(1, 3341, 3346, 80),
(2, 3342, 3346, 90),
(3, 3343, 3346, 75);

-- --------------------------------------------------------

--
-- Table structure for table `topic`
--

CREATE TABLE `topic` (
  `id` int(11) NOT NULL,
  `topicName` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `topic`
--

INSERT INTO `topic` (`id`, `topicName`) VALUES
(1111, 'Web Development'),
(2222, 'Math'),
(3333, 'English');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `emailAddress` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `photoFileName` varchar(500) NOT NULL,
  `userType` enum('Learner','Educator') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `firstName`, `lastName`, `emailAddress`, `password`, `photoFileName`, `userType`) VALUES
(3344, 'Omar', 'Saleh', 'OmarSaleh@Gmail.com', '$2y$10$gUi6/EX9CANcEjJUJ3eQ0O/ZNTDjFVVD3XUw0chSAKEve6UVI7Fqq', 'Omar.png', 'Educator'),
(3345, 'Rana', 'Alotaibi', 'rana@gmail.com', '$2y$10$WfituHJ2HD.L3U5jr68xfePFO34HIk.adA8jiLiS/S1d/VjOOwt4q', 'rana.jpg', 'Educator'),
(3346, 'Sara', 'Mansour', 'sara@gmail.com', '$2y$10$yyqXYnmeRx5z7.Bzy2JJDOys1bnQg/cpFd5i4Ho7CxSN89ylZ8seS', 'sara.jpg', 'Learner');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topicID` (`topicID`),
  ADD KEY `educatorID` (`educatorID`);

--
-- Indexes for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizID` (`quizID`);

--
-- Indexes for table `quizquestion`
--
ALTER TABLE `quizquestion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizID` (`quizID`);

--
-- Indexes for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizID` (`quizID`),
  ADD KEY `learnerID` (`learnerID`);

--
-- Indexes for table `takenquiz`
--
ALTER TABLE `takenquiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizID` (`quizID`),
  ADD KEY `learnerID` (`learnerID`);

--
-- Indexes for table `topic`
--
ALTER TABLE `topic`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emailAddress` (`emailAddress`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3344;

--
-- AUTO_INCREMENT for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quizquestion`
--
ALTER TABLE `quizquestion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3350;

--
-- AUTO_INCREMENT for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `takenquiz`
--
ALTER TABLE `takenquiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `topic`
--
ALTER TABLE `topic`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3334;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3347;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`topicID`) REFERENCES `topic` (`id`),
  ADD CONSTRAINT `quiz_ibfk_2` FOREIGN KEY (`educatorID`) REFERENCES `user` (`id`);

--
-- Constraints for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  ADD CONSTRAINT `quizfeedback_ibfk_1` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`);

--
-- Constraints for table `quizquestion`
--
ALTER TABLE `quizquestion`
  ADD CONSTRAINT `quizquestion_ibfk_1` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`);

--
-- Constraints for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  ADD CONSTRAINT `recommendedquestion_ibfk_1` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`),
  ADD CONSTRAINT `recommendedquestion_ibfk_2` FOREIGN KEY (`learnerID`) REFERENCES `user` (`id`);

--
-- Constraints for table `takenquiz`
--
ALTER TABLE `takenquiz`
  ADD CONSTRAINT `takenquiz_ibfk_1` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`),
  ADD CONSTRAINT `takenquiz_ibfk_2` FOREIGN KEY (`learnerID`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
