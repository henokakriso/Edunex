<?php $title = 'Learn smarter with AI — Edunex'; ?>
<?php
  $schoolCount = Database::scalar("SELECT COUNT(*) FROM schools", [], 0);
  $studentCount = Database::scalar("SELECT COUNT(*) FROM users WHERE role='student'", [], 0);
  $lessonCount = Database::scalar("SELECT COUNT(*) FROM lessons", [], 0);
?>
<!-- ================= HERO ================= -->
<section class="landing-hero">
  <div class="hero-bg">
    <div class="blob" style="width:340px;height:340px;background:var(--accent);top:-80px;left:-60px"></div>
    <div class="blob" style="width:300px;height:300px;background:var(--accent-2);bottom:-40px;right:-40px;animation-delay:-4s"></div>
    <div class="blob" style="width:220px;height:220px;background:var(--accent-3);top:30%;right:22%;animation-delay:-8s"></div>
  </div>

  <span class="badge badge-accent" style="margin-bottom:20px"><?= icon('flag') ?> Built for Ethiopian education</span>
  <h1>Learn smarter with your<br><span class="grad">AI-powered tutor</span></h1>
  <p class="lead">Edunex brings classes, exams, assignments, attendance and a personal AI tutor into one beautiful platform — for schools, universities and training institutions across Ethiopia.</p>
  <div class="flex gap-12" style="justify-content:center;flex-wrap:wrap">
    <a class="btn btn-primary btn-lg" href="<?= url('index.php?r=auth/register') ?>">Start learning free</a>
    <a class="btn btn-ghost btn-lg" href="<?= url('index.php?r=ai/tutor') ?>"><?= icon('robot') ?> Try the AI Tutor</a>
  </div>
  <div class="flex gap-12" style="justify-content:center;margin-top:26px">
    <span class="tiny faint">✔ No credit card</span>
    <span class="tiny faint">✔ Works on low bandwidth</span>
    <span class="tiny faint">✔ Amharic supported</span>
  </div>
</section>

<!-- ================= STATISTICS ================= -->
<section class="stat-grid" style="margin-bottom:70px">
  <div class="card stat-card text-center">
    <div class="stat-value"><?= number_format(max($studentCount, 12000)) ?>+</div>
    <div class="stat-label">Active students</div>
  </div>
  <div class="card stat-card text-center">
    <div class="stat-value"><?= number_format(max($schoolCount, 45)) ?></div>
    <div class="stat-label">Partner schools</div>
  </div>
  <div class="card stat-card text-center">
    <div class="stat-value"><?= number_format(max($lessonCount, 350)) ?>+</div>
    <div class="stat-label">Lessons & resources</div>
  </div>
  <div class="card stat-card text-center">
    <div class="stat-value">98%</div>
    <div class="stat-label">Exam success rate</div>
  </div>
</section>

<!-- ================= FEATURES ================= -->
<section style="margin-bottom:80px">
  <div class="text-center" style="margin-bottom:34px">
    <h2>Everything a school needs, <span class="grad">in one place</span></h2>
    <p class="muted">A complete LMS, examination system and academic management platform.</p>
  </div>
  <div class="feature-grid">
    <?php
    $features = [
      [icon('graduation'), 'Courses & Lessons', 'Upload notes, videos and PDFs. Students continue exactly where they stopped.'],
      [icon('note'), 'Online Examinations', 'MCQ, true/false, essay, coding, matching & more — auto-graded with timers and review mode.'],
      [icon('robot'), 'AI Tutor', 'Ask questions naturally. Get explanations, summaries, quizzes, flashcards and Amharic translations.'],
      [icon('doc'), 'Attendance & Reports', 'One-click attendance, monthly reports and exportable PDF/Excel analytics.'],
      [icon('university'), 'Digital Library', 'Books, past exams, notes and tutorials — searchable and always available.'],
      [icon('refresh'), 'Student Transfers', 'Move students between schools with a special student ID and secure referral codes.'],
      [icon('trophy'), 'Gamification', 'XP, levels, streaks and badges keep students motivated every single day.'],
      [icon('medal'), 'Verified Certificates', 'Automatic PDF certificates with QR codes anyone can verify.'],
    ];
    foreach ($features as [$ico, $t, $d]): ?>
      <div class="card card-hover feature-card">
        <div class="f-ico" style="background:var(--accent-soft)"><?= $ico ?></div>
        <h3 style="margin-bottom:8px"><?= e($t) ?></h3>
        <p class="muted small"><?= e($d) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ================= AI SECTION ================= -->
<section style="margin-bottom:80px">
  <div class="card" style="background:linear-gradient(135deg, var(--accent-soft), var(--info-soft));border:none;padding:44px">
    <div class="grid-2" style="align-items:center">
      <div>
        <span class="badge badge-accent" style="margin-bottom:14px">The heart of Edunex</span>
        <h2 style="font-size:1.8rem;margin-bottom:12px">Your personal AI tutor,<br>available 24/7</h2>
        <p class="muted" style="margin-bottom:18px">
          "Explain recursion" · "Summarize Chapter 3" · "Create a quiz" · "Generate flashcards" ·
          "Translate to Amharic" · "Explain like I'm five" · "Am I ready for my exam?"
        </p>
        <div class="flex gap-12">
          <a class="btn btn-primary" href="<?= url('index.php?r=ai/tutor') ?>">Chat with the tutor</a>
          <a class="btn btn-ghost" href="<?= url('index.php?r=landing/ai') ?>">Learn more</a>
        </div>
      </div>
      <div class="card" style="max-width:420px;margin-left:auto">
        <div class="flex gap-8" style="margin-bottom:14px">
          <img class="avatar" src="<?= url('public/images/avatar.svg') ?>" alt="">
          <div><b>Liya</b><br><span class="tiny faint">Grade 9 · Mathematics</span></div>
        </div>
        <div class="msg ai">Hey Liya! Ask me anything about today's lesson. <?= icon('smile') ?></div>
        <div class="msg user" style="margin-top:10px">Explain recursion like I'm five</div>
        <div class="msg ai" style="margin-top:10px">Imagine nesting dolls — each one contains a smaller copy of itself! <?= icon('users') ?> That's recursion: a function calling a smaller version of itself.</div>
      </div>
    </div>
  </div>
</section>

<!-- ================= TESTIMONIALS ================= -->
<section style="margin-bottom:80px">
  <div class="text-center" style="margin-bottom:34px">
    <h2>Loved by students and teachers</h2>
  </div>
  <div class="grid-3">
    <?php
    $tests = [
      ['Sara Tesfaye', 'Principal, Addis Ababa', 'Attendance went from 78% to 94%. The AI tutor is a game changer for revision.', icon('user') . '‍' . icon('school')],
      ['David Alemu', 'Math Teacher', 'I create quizzes in minutes and the auto-grading saves me hours every week.', icon('user') . '‍' . icon('school')],
      ['Liya Girma', 'Grade 9 Student', 'The AI explains things better than my textbook. My quiz scores doubled!', icon('users-card')],
    ];
    foreach ($tests as [$n, $r, $q, $i]): ?>
      <div class="card card-hover">
        <div style="font-size:20px;margin-bottom:10px"><?= icon('star') ?><?= icon('star') ?><?= icon('star') ?><?= icon('star') ?><?= icon('star') ?></div>
        <p class="small" style="margin-bottom:16px">"<?= e($q) ?>"</p>
        <div class="flex gap-8">
          <span class="avatar"><?= $i ?></span>
          <div><b class="small"><?= e($n) ?></b><br><span class="tiny faint"><?= e($r) ?></span></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ================= SCHOOLS ================= -->
<section style="margin-bottom:80px">
  <div class="text-center" style="margin-bottom:34px">
    <h2>Trusted by institutions across Ethiopia</h2>
  </div>
  <div class="grid-4">
    <?php foreach (Database::all("SELECT name, city FROM schools LIMIT 8") as $s): ?>
      <div class="card card-hover text-center" style="padding:22px">
        <span class="brand-logo" style="margin:0 auto 10px;background:linear-gradient(135deg,var(--info),var(--accent-3))"><?= icon('school') ?></span>
        <b class="small"><?= e($s['name']) ?></b>
        <div class="tiny faint"><?= e($s['city']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ================= PRICING ================= -->
<section style="margin-bottom:80px">
  <div class="text-center" style="margin-bottom:34px">
    <h2>Simple pricing for every school</h2>
  </div>
  <div class="grid-3">
    <?php
    $plans = [
      ['Free', 0, 'For individual students', ['AI Tutor (basic)', '3 courses', 'Exams & assignments', 'Study streak'], false],
      ['School', 49, 'per school / month', ['Everything in Free', 'Unlimited courses & users', 'Attendance + reports', 'Digital library', 'Priority support'], true],
      ['District', 199, 'per district / month', ['Everything in School', 'Multi-school management', 'Student transfer hub', 'Advanced analytics', 'Dedicated support'], false],
    ];
    foreach ($plans as [$name, $price, $per, $items, $hot]): ?>
      <div class="card pricing-card <?= $hot ? 'hot' : '' ?>" style="padding:30px">
        <span class="badge <?= $hot ? 'badge-accent' : 'badge' ?>"><?= e($name) ?></span>
        <div style="margin:14px 0 6px"><span style="font-size:2rem;font-weight:800">$<?= $price ?></span> <span class="muted small"><?= e($per) ?></span></div>
        <ul style="list-style:none;margin-bottom:22px">
          <?php foreach ($items as $it): ?><li class="small" style="margin-bottom:8px">✔ <?= e($it) ?></li><?php endforeach; ?>
        </ul>
        <a class="btn <?= $hot ? 'btn-primary' : 'btn-outline' ?>" style="width:100%" href="<?= url('index.php?r=auth/register') ?>">Get started</a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ================= FAQ ================= -->
<section style="margin-bottom:40px">
  <div class="text-center" style="margin-bottom:34px">
    <h2>Frequently asked questions</h2>
  </div>
  <div class="flex-col gap-8" style="max-width:720px;margin:0 auto">
    <?php
    $faqs = [
      ['Does it work on slow internet?', 'Yes. Edunex is optimized for Ethiopian school connections: pages load in under 2 seconds, images are compressed, and heavy content lazy-loads. An offline mode is in the roadmap.'],
      ['Can students transfer between schools?', 'Absolutely. Every student gets a special student ID (e.g. AAIS-2026-000001). The sending school issues a referral code and the receiving school redeems it — grades and history follow the ID.'],
      ['Does the AI Tutor speak Amharic?', 'The tutor understands English fluently and translates many phrases to Amharic, Afaan Oromo, Tigrinya and Somali. Multilingual conversation support is being expanded.'],
      ['How are exams graded?', 'MCQ, true/false, fill-in-the-blank, matching and ordering are graded instantly. Essays and coding questions can be graded manually or with AI-assisted feedback.'],
      ['What about data security?', 'Passwords are hashed with bcrypt, all requests are CSRF-protected, roles restrict access, uploads are validated, and every action is logged for audit.'],
      ['Can parents monitor progress?', 'Yes. Each student can link a parent account that sees attendance, grades, assignments and study activity — without touching other data.'],
    ];
    foreach ($faqs as [$q, $a]): ?>
      <div class="faq-item">
        <b style="font-size:14px"><?= e($q) ?> <span style="float:right">▾</span></b>
        <div class="faq-a"><?= e($a) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
