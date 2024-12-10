<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Additional responsive styles */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .table td, .table th {
                padding: 0.5rem;
            }
            
            .img-thumbnail {
                max-width: 100px !important;
                height: auto !important;
            }
            
            .card-tools {
                margin-top: 10px;
                text-align: left;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
        
        /* Ensure images are responsive */
        img {
            max-width: 100%;
            height: auto;
        }
        
        /* Improve form layout on small screens */
        .form-group {
            margin-bottom: 1rem;
        }
        
        #preview_image {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">List of Menu Items</h3>
                    <div class="card-tools">
                        <a href="javascript:void(0)" id="add_menu_button" class="btn btn-flat btn-primary btn-sm">
                            <span class="fas fa-plus"></span> Create New
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <colgroup>
                                <col width="10%">
                                <col width="30%">
                                <col width="20%">
                                <col width="30%">
                                <col width="10%">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Image</th>
                                    <th class="text-center">Menu Details</th>
                                    <th class="text-center">Availability</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
    <?php 
    $i = 1;
    $menus = $conn->query("SELECT p.*, c.name as category_name FROM product_list p INNER JOIN category_list c ON c.id = p.category_id ORDER BY p.id ASC");
    while($row = $menus->fetch_assoc()):
    ?>
    <tr>
        <td class="text-center"><?php echo $i++ ?></td>
        <td class="text-center">
            <img src="<?php echo isset($row['img_path']) ? '../assets/img/'.$row['img_path'] : 'https://via.placeholder.com/150' ?>" alt="" class="img-fluid img-thumbnail" style="max-width: 200px; height: 200px; object-fit: cover;">
        </td>
        <td>
            <p><b>Name:</b> <?php echo $row['name'] ?></p>
            <p><b>Category:</b> <?php echo $row['category_name'] ?></p>
            <p><b>Description:</b> <?php echo $row['description'] ?></p>
            <p><b>Price:</b> <?php echo number_format($row['price'], 2) ?></p>
            <p><b>Size:</b> <?php echo $row['size'] . ' ' . $row['size_unit']; ?></p>
            <p><b>Stock:</b> <?php echo $row['stock'] ?></p>
        </td>
        <td class="text-center">
            <p><?php echo $row['status'] == 'Available' ? 'Available' : 'Unavailable' ?></p>
        </td>
        <td class="text-center">
            <div class="btn-group-vertical">
                <button class="btn btn-sm btn-primary edit_menu mb-1" type="button" 
                        data-id="<?php echo $row['id'] ?>"
                        data-name="<?php echo $row['name'] ?>"
                        data-status="<?php echo $row['status'] ?>"
                        data-description="<?php echo $row['description'] ?>"
                        data-price="<?php echo $row['price'] ?>"
                        data-category_id="<?php echo $row['category_id'] ?>"
                        data-size="<?php echo $row['size'] ?>"
                        data-size_unit="<?php echo $row['size_unit'] ?>"
                        data-img_path="<?php echo $row['img_path'] ?>"
                        data-stock="<?php echo $row['stock'] ?>">Edit
                </button>
                <button class="btn btn-sm btn-danger delete_menu" type="button" data-id="<?php echo $row['id'] ?>">Delete</button>
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
    </div>
</div>

<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-12" id="manage-menu-form" style="display: none;">
            <div class="card card-outline card-primary">
                <form action="" id="manage-menu">
                    <div class="card-header">
                        <h3 class="card-title">Menu Form</h3>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="id">
                        <div class="form-group">
                            <label class="control-label">Menu Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Menu Description</label>
                            <textarea cols="30" rows="3" class="form-control" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="availability">Availability</label>
                            <select name="status" class="form-control" id="availability">
                                <option value="Available">Available</option>
                                <option value="Unavailable">Unavailable</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Category</label>
                            <select name="category_id" class="custom-select browser-default" required>
                                <option value="">Select Category</option>
                                <?php
                                $categories = $conn->query("SELECT * FROM category_list ORDER BY name ASC");
                                while($row = $categories->fetch_assoc()):
                                ?>
                                <option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="control-label">Price</label>
                                <input type="number" class="form-control text-left" name="price" step="any" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="stock">Stock</label>
                                <input type="number" class="form-control" name="stock" id="stock" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label for="size">Size:</label>
                                <input type="text" name="size" id="size" class="form-control" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="size_unit">Size Unit:</label>
                                <select name="size_unit" id="size_unit" class="form-control" required>
                                    <option value="inches">Inches</option>
                                    <option value="cm">Centimeters</option>
                                    <option value="mm">Millimeters</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Image</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="img" id="customFile" onchange="displayImg(this)">
                                <label class="custom-file-label" for="customFile">Choose file</label>
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <img src="" alt="" id="preview_image" class="img-fluid" style="max-height: 200px;">
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary mr-2">Save</button>
                                <button type="button" class="btn btn-secondary" onclick="$('#manage-menu').get(0).reset(); $('#preview_image').attr('src', ''); $('#manage-menu-form').hide();">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<script>
    // File input label update
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

    function displayImg(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#preview_image').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Function to scroll to the menu form
    function scrollToMenuForm() {
        $('html, body').animate({
            scrollTop: $("#manage-menu-form").offset().top - 100 // Added offset to prevent form being hidden behind fixed headers
        }, 500);
        $('#manage-menu-form').show();
    }

    // Create New button click handler
    $('#add_menu_button').click(function() {
        // Reset the form when creating a new menu item
        $('#manage-menu')[0].reset();
        $('#preview_image').attr('src', '');
        
        // Show and scroll to the form
        scrollToMenuForm();
    });

    // Edit button click handler
    $('.edit_menu').click(function() {
        // Populate form fields with existing data
        $('input[name=id]').val($(this).attr('data-id'));
        $('input[name=name]').val($(this).attr('data-name'));
        $('textarea[name=description]').val($(this).attr('data-description'));
        $('input[name=price]').val($(this).attr('data-price'));
        $('select[name=status]').val($(this).attr('data-status'));
        $('select[name=category_id]').val($(this).attr('data-category_id'));
        $('input[name=size]').val($(this).attr('data-size'));
        $('select[name=size_unit]').val($(this).attr('data-size_unit'));
        $('#preview_image').attr('src', $(this).attr('data-img_path'));
        $('input[name=stock]').val($(this).attr('data-stock'));
        
        // Show and scroll to the form
        scrollToMenuForm();
    });

    // Rest of the script remains the same
    $('#manage-menu').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'ajax.php?action=save_menu',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(resp) {
                if (resp == 1) {
                    Swal.fire('Success!', 'Menu item saved successfully.', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    Swal.fire('Error!', 'An error occurred. Please try again.', 'error');
                }
            }
        });
    });

    // Remaining delete functionality stays the same
    $('.delete_menu').click(function() {
        const menu_id = $(this).attr('data-id');
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
                    url: 'ajax.php?action=delete_menu',
                    method: 'POST',
                    data: { id: menu_id },
                    success: function(resp) {
                        if (resp == 1) {
                            Swal.fire('Deleted!', 'Your menu item has been deleted.', 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            Swal.fire('Error!', 'An error occurred. Please try again.', 'error');
                        }
                    }
                });
            }
        });
    });
</script>
</body>
</html>