<?php
include 'db_connect.php';
$qry = $conn->query("SELECT * FROM system_settings LIMIT 1");
$meta = array(); // Initialize meta array
if ($qry->num_rows > 0) {
    foreach ($qry->fetch_array() as $k => $val) {
        $meta[$k] = $val;
    }
}
?>
<div class="container-fluid">
    <div class="card col-lg-12">
        <div class="card-body">
            <form action="" id="manage-settings">
                <div class="form-group">
                    <label for="name" class="control-label">System Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($meta['name']) ? $meta['name'] : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="email" class="control-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($meta['email']) ? $meta['email'] : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="contact" class="control-label">Contact</label>
                    <input type="text" class="form-control" id="contact" name="contact" value="<?php echo isset($meta['contact']) ? $meta['contact'] : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="about" class="control-label">About Content</label>
                    <textarea name="about" class="text-jqte"><?php echo isset($meta['about_content']) ? $meta['about_content'] : '' ?></textarea>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Image</label>
                    <input type="file" class="form-control" name="img" onchange="displayImg(this, $(this))">
                </div>
                <div class="form-group">
                    <img src="<?php echo isset($meta['cover_img']) ? '../assets/img/' . $meta['cover_img'] : '' ?>" alt="" id="cimg">
                </div>
                <center>
                    <button class="btn btn-info btn-primary btn-block col-md-2">Save</button>
                </center>
            </form>
        </div>
    </div>
    <style>
        img#cimg {
            max-height: 10vh;
            max-width: 6vw;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        function displayImg(input, _this) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#cimg').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $('.text-jqte').jqte();

        $('#manage-settings').submit(function(e) {
            e.preventDefault();
            start_load();
            $.ajax({
                url: 'ajax.php?action=save_settings',
                data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                error: err => {
                    console.log(err);
                },
                success: function(resp) {
                    if (resp == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Data successfully saved.',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!'
                        });
                    }
                }
            });
        });
    </script>
</div>
