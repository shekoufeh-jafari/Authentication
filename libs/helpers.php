<?php

function assets(string $path): string
{
    return site_url('/assets/' . $path);
}

function site_url(string $uri = '')
{
    return BASE_URL . $uri;
}

function redirect(string $target = BASE_URL): void
{
    header('Location: ' . $target);
    die();
}

function setErrorAndRedirect(string $message, string $target): void
{
    $_SESSION['error'] = $message;
    redirect(site_url($target));
}
