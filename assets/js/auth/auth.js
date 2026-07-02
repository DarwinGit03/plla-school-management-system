<script>
$("#loginForm").submit(function(e){

    e.preventDefault();

    $.ajax({

        url: "<?= base_url('auth/login') ?>",

        type: "POST",

        data: $(this).serialize(),

        dataType: "json",

        success: function(res){

            if(res.status){

                Swal.fire({
                    icon:'success',
                    title:'Success',
                    text:res.message
                }).then(function(){

                    window.location = res.redirect;

                });

            }else{

                Swal.fire({
                    icon:'error',
                    title:'Error',
                    text:res.message
                });

            }

        },

        error:function(){

            Swal.fire({
                icon:'error',
                title:'Error',
                text:'Server error'
            });

        }

    });

});
</script>