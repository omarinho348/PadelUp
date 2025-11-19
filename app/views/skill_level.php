<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skill Level - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/skill_level.css">
</head>
<body>
<?php 
require_once __DIR__ . '/../controllers/SkillLevelController.php';
require_once __DIR__ . '/../controllers/UserController.php';

// 1. Handle form submission
$score = SkillLevelController::calculate();

// 2. If it's a GET request, check if a logged-in user already has a score
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SESSION['user_id'])) {
    $profile = UserController::getPlayerProfile();
    if ($profile && isset($profile['skill_level']) && $profile['skill_level'] > 0) {
        // User has an existing score, so we'll display it
        $score = $profile['skill_level'];
    }
}

// Determine whether to show the questionnaire or the result
$showQuestionnaire = ($score === null);

include __DIR__ . '/partials/navbar.php'; 
?>

<div class="container" style="padding-top: 80px; min-height: 70vh;">
    <div class="main-heading" style="padding-bottom: 40px;">
        <h1>Your Padel Skill Level</h1>
    </div>

    <div class="questionnaire-container">
        <div class="progress-bar-container" style="<?php if (!$showQuestionnaire) echo 'display:none;'; ?>">
            <div class="progress-bar">
                <div class="progress-bar-inner"></div>
            </div>
        </div>

        <form id="skillLevelForm" class="questionnaire-form" method="POST" action="skill_level.php" style="<?php if (!$showQuestionnaire) echo 'display:none;'; ?>">
            <!-- Question 1: Net Play -->
            <div class="question-step active">
                <h3>How would you rate your net play skills?</h3>
                <div class="options-grid">
                    <label class="option-card"><input type="radio" name="net_play" value="1.00"><div class="option-card-content"><span class="icon">🚧</span><div class="text"><strong>Beginner:</strong><small>I'm still learning to volley and often stay at the back.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="net_play" value="2.00"><div class="option-card-content"><span class="icon">🛠️</span><div class="text"><strong>Improver:</strong><small>I can hit basic volleys but lack consistency and power.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="net_play" value="3.00"><div class="option-card-content"><span class="icon">🎯</span><div class="text"><strong>Intermediate:</strong><small>I'm comfortable at the net and can handle fast exchanges.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="net_play" value="4.00"><div class="option-card-content"><span class="icon">⚡️</span><div class="text"><strong>Advanced:</strong><small>I dominate the net with aggressive volleys and bandejas.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="net_play" value="5.00"><div class="option-card-content"><span class="icon">🏆</span><div class="text"><strong>Expert:</strong><small>My net play is a major weapon with precision and power.</small></div></div></label>
                </div>
            </div>

            <!-- Question 2: Glass Play -->
            <div class="question-step">
                <h3>How comfortable are you playing shots off the glass?</h3>
                <div class="options-grid">
                    <label class="option-card"><input type="radio" name="glass_play" value="1.00"><div class="option-card-content"><span class="icon">🧱</span><div class="text"><strong>Beginner:</strong><small>I avoid it and prefer to hit the ball before it reaches the glass.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="glass_play" value="2.00"><div class="option-card-content"><span class="icon">🤔</span><div class="text"><strong>Improver:</strong><small>I can return slow balls off the back glass, but struggle with the side.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="glass_play" value="3.00"><div class="option-card-content"><span class="icon">👍</span><div class="text"><strong>Intermediate:</strong><small>I can consistently play balls off the back and side glass with control.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="glass_play" value="4.00"><div class="option-card-content"><span class="icon">🧠</span><div class="text"><strong>Advanced:</strong><small>I use the glass strategically to defend and set up attacking shots.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="glass_play" value="5.00"><div class="option-card-content"><span class="icon">😎</span><div class="text"><strong>Expert:</strong><small>I can execute complex shots like "doble pared" (double wall) with ease.</small></div></div></label>
                </div>
            </div>

            <!-- Question 3: Serve -->
            <div class="question-step">
                <h3>How would you rate your serve?</h3>
                <div class="options-grid">
                    <label class="option-card"><input type="radio" name="serve" value="1.00"><div class="option-card-content"><span class="icon">🎯</span><div class="text"><strong>Beginner:</strong><small>I just focus on getting the ball in play.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="serve" value="2.00"><div class="option-card-content"><span class="icon">➡️</span><div class="text"><strong>Improver:</strong><small>My serve is consistent but lacks variation or strategic placement.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="serve" value="3.00"><div class="option-card-content"><span class="icon">🔄</span><div class="text"><strong>Intermediate:</strong><small>I can vary my serve's speed and placement (to the 'T' or glass).</small></div></div></label>
                    <label class="option-card"><input type="radio" name="serve" value="4.00"><div class="option-card-content"><span class="icon">💥</span><div class="text"><strong>Advanced:</strong><small>My serve is a weapon to set up the point, often forcing a weak return.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="serve" value="5.00"><div class="option-card-content"><span class="icon">🔥</span><div class="text"><strong>Expert:</strong><small>I have a highly effective and varied serve (kick, slice) that puts opponents under pressure.</small></div></div></label>
                </div>
            </div>

            <!-- Question 4: Overheads -->
            <div class="question-step">
                <h3>How comfortable are your overhead shots (bandeja/vibora)?</h3>
                <div class="options-grid">
                    <label class="option-card"><input type="radio" name="overheads" value="1.00"><div class="option-card-content"><span class="icon">🤷</span><div class="text"><strong>Beginner:</strong><small>I struggle with overheads and often let the ball drop.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="overheads" value="2.00"><div class="option-card-content"><span class="icon">💪</span><div class="text"><strong>Improver:</strong><small>I can hit a basic bandeja to keep the ball in play.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="overheads" value="3.00"><div class="option-card-content"><span class="icon">🔨</span><div class="text"><strong>Intermediate:</strong><small>I can hit both bandejas and viboras with decent consistency.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="overheads" value="4.00"><div class="option-card-content"><span class="icon">🎯</span><div class="text"><strong>Advanced:</strong><small>I can vary my overheads with spin, speed, and placement to attack.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="overheads" value="5.00"><div class="option-card-content"><span class="icon">🚀</span><div class="text"><strong>Expert:</strong><small>My overheads are a defining part of my game, used to finish points.</small></div></div></label>
                </div>
            </div>

            <!-- Question 5: Positioning -->
            <div class="question-step">
                <h3>How well do you position yourself on the court?</h3>
                <div class="options-grid">
                    <label class="option-card"><input type="radio" name="positioning" value="1.00"><div class="option-card-content"><span class="icon">📍</span><div class="text"><strong>Beginner:</strong><small>I'm often unsure where to stand or move.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="positioning" value="2.00"><div class="option-card-content"><span class="icon">🚶</span><div class="text"><strong>Improver:</strong><small>I understand basic positioning but can be slow to react.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="positioning" value="3.00"><div class="option-card-content"><span class="icon">🏃</span><div class="text"><strong>Intermediate:</strong><small>I have good court awareness and move well with my partner.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="positioning" value="4.00"><div class="option-card-content"><span class="icon">♟️</span><div class="text"><strong>Advanced:</strong><small>I anticipate shots and position myself to gain a tactical advantage.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="positioning" value="5.00"><div class="option-card-content"><span class="icon">🔮</span><div class="text"><strong>Expert:</strong><small>My court positioning is intuitive and almost always optimal.</small></div></div></label>
                </div>
            </div>

            <!-- Question 6: Competition Experience -->
            <div class="question-step">
                <h3>What is your competition experience level?</h3>
                <div class="options-grid">
                    <label class="option-card"><input type="radio" name="competition" value="1.00"><div class="option-card-content"><span class="icon">👋</span><div class="text"><strong>None:</strong><small>I only play friendly matches with friends.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="competition" value="2.00"><div class="option-card-content"><span class="icon">🏅</span><div class="text"><strong>Local/Club:</strong><small>I've played in a few local or club-level tournaments.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="competition" value="3.00"><div class="option-card-content"><span class="icon">🏆</span><div class="text"><strong>Regional:</strong><small>I regularly compete in regional or city-wide tournaments.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="competition" value="4.00"><div class="option-card-content"><span class="icon">🌍</span><div class="text"><strong>National:</strong><small>I have competed in national-level tournaments.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="competition" value="5.00"><div class="option-card-content"><span class="icon">🌟</span><div class="text"><strong>Professional:</strong><small>I play or have played on a professional or semi-pro circuit.</small></div></div></label>
                </div>
            </div>

            <!-- Question 7: Time Played -->
            <div class="question-step">
                <h3>How long have you played padel?</h3>
                <div class="options-grid">
                    <label class="option-card"><input type="radio" name="time_played" value="1.00"><div class="option-card-content"><span class="icon">👶</span><div class="text"><strong>Less than 6 months</strong></div></div></label>
                    <label class="option-card"><input type="radio" name="time_played" value="2.00"><div class="option-card-content"><span class="icon">🧑</span><div class="text"><strong>6 months to 2 years</strong></div></div></label>
                    <label class="option-card"><input type="radio" name="time_played" value="3.00"><div class="option-card-content"><span class="icon">👨</span><div class="text"><strong>2 to 5 years</strong></div></div></label>
                    <label class="option-card"><input type="radio" name="time_played" value="4.00"><div class="option-card-content"><span class="icon">👴</span><div class="text"><strong>More than 5 years</strong></div></div></label>
                </div>
            </div>

            <!-- Question 8: Training Background -->
            <div class="question-step">
                <h3>What is your training background?</h3>
                <div class="options-grid">
                    <label class="option-card"><input type="radio" name="training" value="1.00"><div class="option-card-content"><span class="icon">📚</span><div class="text"><strong>Self-taught:</strong><small>I've learned by watching videos and playing.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="training" value="2.00"><div class="option-card-content"><span class="icon">👨‍🏫</span><div class="text"><strong>Informal Lessons:</strong><small>I've had a few lessons with friends or a club coach.</small></div></div></label>
                    <label class="option-card"><input type="radio" name="training" value="3.00"><div class="option-card-content"><span class="icon">🎓</span><div class="text"><strong>Regular Coaching:</strong><small>I take or have taken regular lessons with a certified coach.</small></div></div></label>
                </div>
            </div>

            <!-- Navigation -->
            <div class="navigation-buttons">
                <button type="button" class="btn btn-secondary prev-btn" style="display: none;">Previous</button>
                <button type="button" class="btn btn-primary next-btn">Next</button>
                <button type="submit" class="btn btn-primary btn-submit" style="display: none;">Calculate My Level</button>
            </div>
        </form>

        <!-- Result Display -->
        <div id="resultContainer" class="result-container" style="<?php if ($showQuestionnaire) echo 'display:none;'; ?>">
            <div class="result-card-new">
                <div class="result-score-new">
                    <span><?php echo number_format((float)$score, 2); ?></span>
                </div>
                <div class="result-details-new">
                    <h2 class="result-title-new">Your Estimated Level</h2>
                    <p class="result-level-new">
                        <?php
                            if ($score < 1.5) { echo 'Beginner'; } 
                            else if ($score < 2.8) { echo 'Intermediate'; } 
                            else if ($score < 4.7) { echo 'Advanced'; } 
                            else if ($score < 6.5) { echo 'Professional'; } 
                            else { echo 'Expert'; }
                        ?>
                    </p>
                    <p class="result-description-new">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            Your rating has been saved to your profile.
                        <?php else: ?>
                            Create an account to save your rating and track your progress.
                        <?php endif; ?>
                    </p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="profile.php" class="btn btn-primary">View My Profile</a>
                    <?php else: ?>
                        <a href="signup.php" class="btn btn-primary">Sign Up for Free</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('skillLevelForm');
    if (!form || form.style.display === 'none') {
        return; // Don't run script if form is not displayed
    }

    const steps = Array.from(form.querySelectorAll('.question-step'));
    const nextBtn = form.querySelector('.next-btn');
    const prevBtn = form.querySelector('.prev-btn');

    // selector matches the HTML class:
    const submitBtn = form.querySelector('.btn-submit');

    // PROGRESS BAR: select from the document (it's outside the form)
    const progressBar = document.querySelector('.progress-bar-inner');

    let currentStep = 0;

    function updateButtons() {
        prevBtn.style.display = currentStep > 0 ? 'inline-block' : 'none';
        nextBtn.style.display = currentStep < steps.length - 1 ? 'inline-block' : 'none';
        if (submitBtn) submitBtn.style.display = currentStep === steps.length - 1 ? 'inline-block' : 'none';
    }

    function updateProgress() {
        if (!progressBar) return; // defensive: do nothing if not found
        // keep your original logic: last step -> 100%
        const progress = (currentStep / (steps.length - 1)) * 100;
        progressBar.style.width = progress + '%';
    }

    function showStep(stepIndex) {
        steps.forEach((step, index) => {
            step.classList.toggle('active', index === stepIndex);
        });
        currentStep = stepIndex;
        updateButtons();
        updateProgress();
        // optional: scroll the form into view so the user sees the new step
        form.scrollIntoView({ behavior: 'smooth' });
    }

    nextBtn.addEventListener('click', () => {
        const currentStepElement = steps[currentStep];
        const isChecked = currentStepElement.querySelector('input[type="radio"]:checked');

        if (!isChecked) {
            alert('Please select an option to continue.');
            return;
        }
        if (currentStep < steps.length - 1) {
            showStep(currentStep + 1);
        }
    });

    prevBtn.addEventListener('click', () => {
        if (currentStep > 0) {
            showStep(currentStep - 1);
        }
    });

    form.addEventListener('submit', (e) => {
        const lastStepElement = steps[steps.length - 1];
        if (!lastStepElement.querySelector('input[type="radio"]:checked')) {
            e.preventDefault();
            alert('Please select an option for the last question.');
        }
    });

    // initial
    showStep(0);
});
</script>


<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>