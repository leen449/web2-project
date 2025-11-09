<?php
include 'db_connection.php';

// Retrieve all topics
$topicsQuery = "SELECT * FROM topic";
$topicsResult = $conn->query($topicsQuery);

// Retrieve all educators (for Phase 2, show all educators)
$educatorsQuery = "SELECT * FROM educator";
$educatorsResult = $conn->query($educatorsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recommend a Question</title>
</head>
<body>
    <h2>Recommend a Question</h2>

    <form action="submit_recommended.php" method="POST">
        <label for="question">Question:</label><br>
        <textarea name="question" id="question" required></textarea><br><br>

        <label for="topic">Topic:</label><br>
        <select name="topic" id="topic" required>
            <option value="">Select a topic</option>
            <?php while ($row = $topicsResult->fetch_assoc()) { ?>
                <option value="<?php echo $row['topic_id']; ?>">
                    <?php echo $row['topic_name']; ?>
                </option>
            <?php } ?>
        </select><br><br>

        <label for="educator">Educator:</label><br>
        <select name="educator" id="educator" required>
            <option value="">Select an educator</option>
            <?php while ($row = $educatorsResult->fetch_assoc()) { ?>
                <option value="<?php echo $row['educator_id']; ?>">
                    <?php echo $row['educator_name']; ?>
                </option>
            <?php } ?>
        </select><br><br>

        <button type="submit">Submit</button>
    </form>

</body>
</html>
