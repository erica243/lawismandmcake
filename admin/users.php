<?php 
include('db_connect.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <style>
        .table-responsive {
            margin-top: 20px;
        }
        .modal .btn-close {
            float: right;
            font-size: 1.5rem;
            line-height: 1;
        }
        .table .thead-dark th {
    color: #fff;
    background-color: #3e88d2;
    border-color:#30d6f1;
}
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card col-lg-10 col-md-12">
            <div class="card-body">
                <h4 class="text-center mb-4">User List</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">First Name</th>
                                <th class="text-center">Last Name</th>
                                <th class="text-center">Email</th>
                                <th class="text-center">Mobile</th>
                                <th class="text-center">Address</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $users = $conn->query("SELECT user_id, first_name, last_name, email, mobile, address FROM user_info ORDER BY first_name ASC");
                            $i = 1;
                            while ($row = $users->fetch_assoc()):
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $i++ ?></td>
                                <td><?php echo $row['first_name'] ?></td>
                                <td><?php echo $row['last_name'] ?></td>
                                <td><?php echo $row['email'] ?></td>
                                <td><?php echo $row['mobile'] ?></td>
                                <td><?php echo $row['address'] ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info read_user" data-id="<?php echo $row['user_id']; ?>">Read</button>
                                    <button class="btn btn-sm btn-danger delete_user" data-id="<?php echo $row['user_id']; ?>">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Read User Modal -->
<div class="modal fade" id="readUserModal" tabindex="-1" aria-labelledby="readUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="readUserModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userDetails">
                <!-- User details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.delete_user').on('click', function() {
            var userId = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete_user.php',
                        type: 'POST',
                        data: { user_id: userId },
                        success: function(response) {
                            Swal.fire(
                                'Deleted!',
                                response,
                                'success'
                            ).then(() => {
                                location.reload(); // Refresh the page to see the changes
                            });
                        },
                        error: function() {
                            Swal.fire(
                                'Error!',
                                'Error deleting user. Please try again.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        $('.read_user').on('click', function() {
            var userId = $(this).data('id');
            $.ajax({
                url: 'fetch_user.php',
                type: 'POST',
                data: { user_id: userId },
                success: function(data) {
                    $('#userDetails').html(data);
                    $('#readUserModal').modal('show');
                },
                error: function() {
                    alert('Error fetching user details. Please try again.');
                }
            });
        });
    });
</script>
</body>
</html>
