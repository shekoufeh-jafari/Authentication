<?php

$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.mailtrap.io';
$mail->SMTPAuth = true;
$mail->Port = 2525;
$mail->Username = '1bb33dde47ff17';
$mail->Password = '44599bd8872f7c';
$mail->setFrom('auth@7auth.mg', '7Auth Project');
$mail->isHtml(true);
