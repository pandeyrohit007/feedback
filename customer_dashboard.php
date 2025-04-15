<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: customer_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Customer Dashboard</title>
  <link rel="stylesheet" href="assets/css/dashboard.css" />
</head>
<body>
  <nav class="navbar">
    <div class="logo">Feedback Analyzer</div>
    <div class="user-info">
      Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> |

      <!-- Logout Button -->
      <form action="logout.php" method="post" style="display:inline;">
        <button type="submit" class="logout-btn">Logout</button>
      </form>
    </div>
  </nav>

  <main class="dashboard-container">
    <h1>Customer Dashboard</h1>
    <p>Submit your valuable feedback below:</p>

    <form id="feedbackForm" class="feedback-form">
      <textarea id="feedbackInput" name="feedback" placeholder="Enter your feedback here..." required></textarea>
      <button type="submit">Submit Feedback</button>
    </form>

    <p id="responseMsg" style="margin-top: 15px; color: #444;"></p>
  </main>

  <script>
    async function sendFeedbackToGemini(feedback) {
      const API_KEY = "AIzaSyD7roQlayvnjQRp88Ej-BsQYGMnk_Ja9xw";

      const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${API_KEY}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          contents: [{
            parts: [{
              text: `Please analyze the following customer feedback: "${feedback}". 

Give a very short and concise analysis, and also provide one or two specific suggestions for how a business can make smarter decisions based on this feedback.`
            }]
          }]
        })
      });

      const data = await response.json();
      return data?.candidates?.[0]?.content?.parts?.[0]?.text || "No analysis response from Gemini.";
    }

    document.getElementById("feedbackForm").addEventListener("submit", async function (e) {
      e.preventDefault();

      const feedback = document.getElementById("feedbackInput").value.trim();
      const responseMsg = document.getElementById("responseMsg");

      if (!feedback) {
        responseMsg.textContent = "Please enter feedback.";
        return;
      }

      responseMsg.textContent = "Submitting feedback...";

      try {
        const geminiReply = await sendFeedbackToGemini(feedback);

        const saveResponse = await fetch("submit.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            feedback: feedback,
            analysis: geminiReply
          })
        });

        const saveResult = await saveResponse.json();

        if (saveResult.success) {
          responseMsg.textContent = "✅ Feedback submitted successfully!";
        } else {
          responseMsg.textContent = "❌ Failed to submit feedback: " + saveResult.message;
        }
      } catch (error) {
        console.error("Error:", error);
        responseMsg.textContent = "Something went wrong while submitting feedback.";
      }
    });
  </script>
</body>
</html>
