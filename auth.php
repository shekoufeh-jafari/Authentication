<?php

require "bootstrap/init.php";

if (isLoggedIn()) {
    redirect();
}

deleteExpiredTokens();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_GET['action'];
    $params = $_POST;
    if ($action == 'register') {
        # validation data
        if (empty($params['name']) || empty($params['email']) || empty($params['phone']))
            setErrorAndRedirect('All input fields required!', 'auth.php?action=register');
        if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL))
            setErrorAndRedirect('Enter the valid email address!', 'auth.php?action=register');
        if (isUserExists($params['email'], $params['phone']))
            setErrorAndRedirect('User Exists with this data!', 'auth.php?action=register');

        # requested data is ok
        if (createUser($params)) {
            $_SESSION['phone'] = $params['phone'];
            redirect('auth.php?action=verify');
        }
    }

    if ($action == 'login') {
        # validation data
        if (empty($params['phone']))
            setErrorAndRedirect('Phone is required!', 'auth.php?action=login');
        if (!isUserExists(phone: $params['phone']))
            setErrorAndRedirect('User Not Exists with this phone: <br>' . $params['phone'], 'auth.php?action=login');

        $_SESSION['phone'] = $params['phone'];
        redirect('auth.php?action=verify');
    }

    if ($action == 'verify') {//مقایسه توکن ارسالی با ورودی کاربر
        $token = findTokenByHash($_SESSION['hash'])->token;
        if ($token === $params['token']) {
            $session = bin2hex(random_bytes(32));//شناسایی نشست کاربر
            //همزمان چند کاربر نمیتوانند لاگین باشند
            changeLoginSession($_SESSION['phone'], $session);
            setcookie('auth', $session, time() + 1728000, '/');//بعد از 20 روز
            deleteTokenByHash($_SESSION['hash']);
            unset($_SESSION['hash'], $_SESSION['phone']);
            redirect();
        } else {
            setErrorAndRedirect('The entered Token is wrong!', 'auth.php?action=verify');
        }
    }
}


if (isset($_GET['action']) && $_GET['action'] == 'verify' && !empty($_SESSION['phone'])) {
    if (!isUserExists(phone: $_SESSION['phone']))
        setErrorAndRedirect('User Not Exists with this data!', 'auth.php?action=login');

    if (isset($_SESSION['hash']) && isAliveToken($_SESSION['hash'])) {// ارسال دوباره توکن منقضی نشده
        sendTokenBySms($_SESSION['phone'], findTokenByHash($_SESSION['hash'])->token);
    } else {//ساخت توکن جدید
        $tokenResult = createLoginToken();
        sendTokenBySms($_SESSION['phone'], $tokenResult['token']);
        $_SESSION['hash'] = $tokenResult['hash'];
    }

    include 'tpl/verify-tpl.php';
}

if (isset($_GET['action']) && $_GET['action'] == 'register') {
    include 'tpl/register-tpl.php';
} else {
    include 'tpl/login-tpl.php';
}
