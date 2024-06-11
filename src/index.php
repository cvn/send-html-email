<?php

// load default values from config file
$values = parse_ini_file('defaults.ini');

?>

<!DOCTYPE html>
<html>
<head>
    <title>Send HTML Email</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="scrollable">
        <div class="container">
            <h1>Send HTML Email</h1>
            <p>Send an unmodified HTML email. Good for exported templates from MailChimp, etc.</p>
            <form method="POST" action="send.php" enctype="multipart/form-data">
                <div class="service">
                    <div class="bordered">
                        <div class="form-row">
                            <label for="username">Gmail account *</label>
                            <input type="text" name="username" id="username" value="<?= $values['username'] ?? '' ?>" required>
                        </div>
                        <div class="form-row">
                            <label for="password">Password *</label>
                            <input type="text" name="password" id="password" value="<?= $values['password'] ?? '' ?>" required>
                        </div>
                    </div>
                    <div class="info">If your Google account has 2-factor authentication enabled, you must use an <a href="https://myaccount.google.com/apppasswords">app password</a>.</div>
                </div>
        
                <div class="bordered">
                    <div class="form-row">
                        <label for="from">From *</label>
                        <input type="text" name="from" id="from" placeholder="My Name <me@example.com>" value="<?= $values['from'] ?? '' ?>" required>
                    </div>
                    <div class="form-row">
                        <label for="to">To</label>
                        <textarea name="to" id="to"><?= $values['to'] ?? '' ?></textarea>
                    </div>
                    <div class="form-row">
                        <label for="cc">Cc</label>
                        <textarea name="cc" id="cc"><?= $values['cc'] ?? '' ?></textarea>
                    </div>
                    <div class="form-row">
                        <label for="bcc">Bcc</label>
                        <textarea name="bcc" id="bcc"><?= $values['bcc'] ?? '' ?></textarea>
                    </div>
                    <div class="form-row">
                        <label for="subject">Subject *</label>
                        <input type="text" name="subject" id="subject" value="<?= $values['subject'] ?? '' ?>" required>
                    </div>
                    <div class="form-row">
                        <label for="htmlfile">Body *</label>
                        <input type="file" name="htmlfile" id="htmlfile" accept=".html" required>
                    </div>
                    <iframe class="preview" id="preview" src="">preview</iframe>
                </div>

                <p></p>

                <div>
                    <div class="chonk-container">
                        <button id="submit" type="submit" class="chonk">Send</button>
                        <span class="chonk-shadow"></span>
                    </div>
                    <span id="spinner" class="spinner hide"></span>
                </div>

                <div id="status" class="status"></div>
            </form>
        </div>
    </div>

<script>
// preview html file

const htmlfile = document.getElementById('htmlfile');
const preview = document.getElementById('preview');

htmlfile.addEventListener('change', function() {
    var file = htmlfile.files[0]
    var src = file ? URL.createObjectURL(file) : ''
    preview.src = src;
});


// submit form

function throwServerErrors(r) {
  if (!r.ok) { throw r }
  return r
}

function submitForm(formData) {
    // disable submit button
    document.getElementById('submit').disabled = true
    document.getElementById('submit').classList.add('active')
    document.getElementById('spinner').classList.remove('hide')
    document.getElementById('status').innerHTML = ''

    var alert
    return fetch('send.php', {
        method: 'POST',
        body: formData,
    })
    .then(throwServerErrors)
    .then(async function(data) {
        data = await data.text()
        alert = `<div class="alert success">${data}</div>`
    })
    .catch(async function(error) {
        error = (await error.text?.()) || error.statusText
        alert = `<div class="alert error">${error}</div>`
    })
    .finally(function() {
        document.getElementById('submit').disabled = false
        document.getElementById('submit').classList.remove('active')
        document.getElementById('spinner').classList.add('hide')
        document.getElementById('status').innerHTML = alert
    })

}

document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault()
    submitForm(new FormData(this))
})
</script>

</body>
</html>
