<?
defined('C5_EXECUTE') or die("Access Denied.");

$subject = SITE.' '.t("Registration - A New User Has Registered");

/**
 * HTML BODY START
 */
ob_start();

?>
<h2><?= t('New User Registration') ?></h2>
<?= t('A new user has registered on your website.') ?><br />
<br />
<?= t('User Name') ?>: <b><?= $uName ?></b><br />
<?= t('Email Address') ?>: <b><?= $uEmail ?></b><br />
<br />
<? if($attribs): ?>
	<ul>
	<? foreach($attribs as $item): ?>
		<li><?= $item ?></li>
	<? endforeach ?>
	</ul>
<? endif ?>
<br />
<? t('This account may be managed directly at') ?><br />
<a href="<?= BASE_URL.View::url('/dashboard/users/search?uID='.$uID) ?>"><?= BASE_URL.View::url('/dashboard/users/search?uID='.$uID) ?></a>
<?

$bodyHTML = ob_get_clean();
/**
 * HTML BODY END
 *
 * ======================
 *
 * PLAIN TEXT BODY START
 */
ob_start();

?>
<?= t('New User Registration') ?>

<?= t('A new user has registered on your website.') ?>

<?= t('User Name') ?>: <?= $uName ?>

<?= t('Email Address') ?>: <?= $uEmail ?>

<? if($attribs): ?>
	<? foreach($attribs as $item): ?>
		<?= $item ?>

	<? endforeach ?>
<? endif ?>

<? t('This account may be managed directly at') ?>

<?= BASE_URL.View::url('/dashboard/users/search?uID='.$uID) ?>
<?

$body = ob_get_clean();
ob_end_clean();
/**
 * PLAIN TEXT BODY END
 */
