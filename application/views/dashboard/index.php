<h2>

Welcome,

<?= $current_user->first_name; ?>

</h2>

<p>

Role ID :

<?= $current_user->role_id; ?>

</p>

<a href="<?= base_url('logout');?>">

Logout

</a>