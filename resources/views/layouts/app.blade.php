<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', 'URL Shortener')
    </title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" >

    @stack('styles')

</head>

<body>

    @yield('content')


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>

        // Laravel CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    </script>

<script>
    $(document).ready(function () {

        $('#logoutButton').on('click', function () {

            $.ajax({
                url: "{{ route('logout') }}",
                type: "GET",

                success: function (response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    }
                },

                error: function () {
                    alert('Logout failed!');
                }
            });

        });

    });
</script>

@stack('scripts')
</body>
</html>
