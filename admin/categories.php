<?php include('db_connect.php');?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Responsive adjustments */
        body {
            background-color: ;
        }

        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        td {
            vertical-align: middle !important;
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }

            .card-header {
                padding: 10px;
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .table td, .table th {
                padding: 0.5rem;
            }

            /* Adjust button layout on small screens */
            .card-footer .btn {
                margin-bottom: 5px;
                width: 100%;
            }
        }

        /* Improve form layout */
        .form-group {
            margin-bottom: 1rem;
        }

        /* Make action buttons more touch-friendly */
        .btn-action {
            margin: 2px;
        }
		.bg-primary {
    background-color: #7ab1ed !important;
}
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="row">
                <!-- FORM Panel -->
                <div class="col-md-4">
                    <form action="" id="manage-category">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                Category Form
                            </div>
                            <div class="card-body">
                                <input type="hidden" name="id">
                                <div class="form-group">
                                    <label class="control-label">Category</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-center">
                                        <button class="btn btn-primary btn-sm mr-2" type="submit">Save</button>
                                        <button class="btn btn-secondary btn-sm" type="button" onclick="$('#manage-category')[0].reset()">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Table Panel -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            Category List
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $i = 1;
                                        $cats = $conn->query("SELECT * FROM category_list order by id asc");
                                        while($row=$cats->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td class="text-center"><?php echo $i++ ?></td>
                                            <td class="text-center">
                                                <?php echo $row['name'] ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button class="btn btn-primary edit_cat btn-action" type="button" 
                                                            data-id="<?php echo $row['id'] ?>" 
                                                            data-name="<?php echo $row['name'] ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-danger delete_cat btn-action" type="button" 
                                                            data-id="<?php echo $row['id'] ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Table Panel -->
            </div>
        </div>	
    </div>
</div>

<!-- Required Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<script>
    $(document).ready(function() {
        // Form submission handler
        $('#manage-category').submit(function(e){
            e.preventDefault();
            $.ajax({
                url: 'ajax.php?action=save_category',
                data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                success: function(resp) {
                    if (resp == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data successfully added',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            location.reload();
                        });
                    } else if (resp == 2) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data successfully updated',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            location.reload();
                        });
                    }
                }
            });
        });

        // Edit category handler
        $('.edit_cat').click(function(){
            var cat = $('#manage-category');
            cat[0].reset();
            cat.find("[name='id']").val($(this).attr('data-id'));
            cat.find("[name='name']").val($(this).attr('data-name'));
        });

        // Delete category handler
        $('.delete_cat').click(function(){
            var id = $(this).attr('data-id');
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
                    delete_cat(id);
                }
            });
        });

        // Delete category function
        function delete_cat(id) {
            $.ajax({
                url: 'ajax.php?action=delete_category',
                method: 'POST',
                data: {id: id},
                success: function(resp) {
                    if (resp == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data successfully deleted',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            location.reload();
                        });
                    }
                }
            });
        }
    });
</script>
</body>
</html>