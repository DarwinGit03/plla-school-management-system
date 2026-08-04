
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script> <!-- notification -->

<script>
    const CSRF = {
        name: "<?= $this->security->get_csrf_token_name(); ?>",
        hash: "<?= $this->security->get_csrf_hash(); ?>"
    };
</script>

<?php if(isset($page_js)): ?>
    <script src="<?= base_url($page_js); ?>"></script>
<?php endif; ?>

<!-- session -->
<?php if($this->session->userdata('user_id')): ?>
    <script src="<?=base_url()?>assets/js/session.js"></script>
<?php endif; ?>


</body>

</html>