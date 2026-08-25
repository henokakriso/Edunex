<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
</div>

<div class="card" style="max-width:500px">
  <div class="card-head"><h2>Enter Fix Token</h2></div>
  <form method="POST" style="padding:1.5rem">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Fix Token</label>
      <input type="text" name="token" required class="form-control" placeholder="Paste the token from the user..." style="font-family:monospace">
    </div>
    <p style="color:var(--muted);font-size:0.85rem;margin-bottom:1rem">
      The token grants access ONLY to the page the user reported an issue with. You cannot see any other data or settings.
    </p>
    <button class="btn btn-primary" type="submit">Start Fix Session</button>
  </form>
</div>
