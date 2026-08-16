<?php $title = 'FAQ'; ?>
<div class="landing-hero" style="padding:60px 0 40px">
  <h1>Frequently asked <span class="grad">questions</span></h1>
</div>
<div class="flex-col gap-8" style="max-width:720px;margin:0 auto">
  <?php
  $faqs = [
    ['What hardware does my school need?', 'A single Linux server (or shared hosting) running Apache + PHP 8 + MySQL is enough. Everything else runs in the browser.'],
    ['How does the student transfer system work?', 'Every student has a special student ID (e.g. AAIS-2026-000001). The sending school creates a transfer request with a referral code; the receiving school redeems the code and the student record — including grades — is transferred.'],
    ['Can I use my own questions?', 'Yes. Teachers can build exams from scratch with 10 question types, or use the AI to generate questions from lesson content.'],
    ['Is my data safe?', 'All passwords are bcrypt-hashed, sessions are secure, uploads are validated, and a full audit log tracks who did what, when.'],
    ['Does it work offline?', 'The web platform needs an internet connection, but it is optimized for slow connections. Offline synchronization for mobile apps is on the roadmap.'],
    ['What languages are supported?', 'The interface is in English with Amharic-first design; Amharic, Afaan Oromo, Tigrinya and Somali are supported for translation and being added to the UI.'],
  ];
  foreach ($faqs as [$q, $a]): ?>
    <div class="faq-item">
      <b style="font-size:14px"><?= e($q) ?> <span style="float:right">▾</span></b>
      <div class="faq-a"><?= e($a) ?></div>
    </div>
  <?php endforeach; ?>
</div>
