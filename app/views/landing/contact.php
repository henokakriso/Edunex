<?php $title = 'Contact'; ?>
<div class="landing-hero" style="padding:60px 0 40px">
  <h1>Talk to the <span class="grad">team</span></h1>
  <p class="lead">Questions, demos, or partnership — we'd love to hear from you.</p>
</div>
<div class="card" style="max-width:560px;margin:0 auto">
  <form method="post">
    <?= csrf_field() ?>
    <div class="field"><label>Name</label><input class="input" name="name" required></div>
    <div class="field"><label>Email</label><input class="input" type="email" name="email" required></div>
    <div class="field"><label>Message</label><textarea class="input" name="message" required></textarea></div>
    <button class="btn btn-primary" style="width:100%">Send message</button>
  </form>
</div>
