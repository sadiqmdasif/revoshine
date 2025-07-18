<?php if ( isset( $_SESSION["alert"] ) ) :

	$alert = [
		'icon'    => $_SESSION["alert"]['type'] == 'success' ? 'success' : 'error',
		'title'   => $_SESSION["alert"]['title'],
		'message' => $_SESSION["alert"]['message']
	];

	?>

    <script>
        let timerInterval;

        Swal.fire({
            icon: '<?php echo $alert['icon'] ?>',
            title: '<?php echo $alert['title'] ?>',
            html: '<?php echo $alert['message'] ?>',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
            didOpen: () => {
                const timer = Swal.getPopup().querySelector("b");

                timerInterval = setInterval(() => {
                    timer.textContent = `${Swal.getTimerLeft()}`;
                }, 100);
            },
            willClose: () => {
                clearInterval(timerInterval);
            }
        });
    </script>

	<?php unset( $_SESSION["alert"] ) ?>

<?php endif ?>