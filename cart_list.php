<!-- SweetAlert2 CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<header class="masthead">
    <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-center text-center">
            <div class="col-lg-10 align-self-center mb-4 page-title">
                <h1 class="text-white">Shopping Cart</h1>
                <hr class="divider my-4 bg-dark" />
            </div>
        </div>
    </div>
</header>
<section class="page-section" id="menu">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="sticky">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8"><b>Items</b></div>
                                <div class="col-md-4 text-right"><b>Total</b></div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php 
                $shipping_amount = 0; // Initialize shipping amount

                if(isset($_SESSION['login_user_id'])){
                    $data = "where c.user_id = '".$_SESSION['login_user_id']."' ";  
                    
                   $user_query = $conn->query("SELECT address FROM user_info WHERE user_id = '".$_SESSION['login_user_id']."'");
                    $user_info = $user_query->fetch_assoc();
                    $user_address = $user_info['address'];
                    // Fetch shipping fee based on address
                    $shipping_query = $conn->query("SELECT shipping_amount FROM shipping_info WHERE address = '$user_address' ");
                    if ($shipping_query->num_rows > 0) {
                        $shipping_info = $shipping_query->fetch_assoc();
                        $shipping_amount = $shipping_info['shipping_amount'];
                    }
                } else {
                    $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
                    $data = "where c.client_ip = '".$ip."' ";  
                }

                $total = 0;
                $get = $conn->query("SELECT *,c.id as cid FROM cart c inner join product_list p on p.id = c.product_id ".$data);
                while($row= $get->fetch_assoc()):
                    $total += ($row['qty'] * $row['price']);
                ?>

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 d-flex align-items-center" style="text-align: -webkit-center">
                                <div class="col-auto"> 
                                    <a href="admin/ajax.php?action=delete_cart&id=<?php echo $row['cid'] ?>" class="rem_cart btn btn-sm btn-outline-danger" data-id="<?php echo $row['cid'] ?>"><i class="fa fa-trash"></i></a>
                                </div>  
                                <div class="col-auto flex-shrink-1 flex-grow-1 text-center"> 
                                    <img src="assets/img/<?php echo $row['img_path'] ?>" alt="">
                                </div>  
                            </div>
                            <div class="col-md-4">
                                <p><b><large><?php echo $row['name'] ?></large></b></p>
                                <p class='truncate'> <b><small>Desc :<?php echo $row['description'] ?></small></b></p>
                                <p> <b><small>Unit Price :<?php echo number_format($row['price'],2) ?></small></b></p>
                                <p><small>QTY :</small></p>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <button class="btn btn-outline-secondary qty-minus" type="button" data-id="<?php echo $row['cid'] ?>" data-price="<?php echo $row['price'] ?>" data-stock="<?php echo $row['stock'] ?>"><span class="fa fa-minus"></span></button>
                                    </div>
                                    <input type="number" readonly value="<?php echo $row['qty'] ?>" min="1" class="form-control text-center" name="qty">
                                    <div class="input-group-prepend">
                                        <button class="btn btn-outline-secondary qty-plus" type="button" data-id="<?php echo $row['cid'] ?>" data-price="<?php echo $row['price'] ?>" data-stock="<?php echo $row['stock'] ?>"><span class="fa fa-plus"></span></button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <b><large class="item-total"><?php echo number_format($row['qty'] * $row['price'],2) ?></large></b>
                            </div>
                        </div>
                    </div>
                </div>

                <?php endwhile; ?>
            </div>
            <div class="col-md-4">
                <div class="sticky">
                    <div class="card">
                        <div class="card-body">
                            <p><large>Total Amount</large></p>
                            <hr>
                            <p class="text-right"><b id="total-amount"><?php echo number_format($total,2) ?></b></p>
                            <p class="text-right"><b>Shipping Fee:</b> <span id="shipping-amount"><?php echo number_format($shipping_amount, 2); ?></span></p>
                            <p class="text-right"><b>Total with Shipping:</b> <span id="total-with-shipping"><?php echo number_format($total + $shipping_amount, 2); ?></span></p>
                            <hr>
                            <div class="text-center">
                                <button class="btn btn-block btn-outline-dark" type="button" id="checkout">Proceed to Checkout</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .card p {
        margin: unset
    }
    .card img{
        max-width: calc(100%);
        max-height: calc(59%);
    }
    div.sticky {
        position: -webkit-sticky; /* Safari */
        position: sticky;
        top: 4.7em;
        z-index: 10;
        background: white
    }
    .rem_cart{
        position: absolute;
        left: 0;
    }
</style>

<script>
    $('.view_prod').click(function(){
        uni_modal_right('Product','view_prod.php?id='+$(this).attr('data-id'))
    })

    $('.qty-minus').click(function(){
        var qty = $(this).parent().siblings('input[name="qty"]').val();
        var stock = $(this).attr('data-stock');
        if(qty == 1){
            return false;
        }
        update_qty(parseInt(qty) - 1, $(this).attr('data-id'), $(this).attr('data-price'));
        $(this).parent().siblings('input[name="qty"]').val(parseInt(qty) - 1);
    })

    $('.qty-plus').click(function(){
        var qty = $(this).parent().siblings('input[name="qty"]').val();
        var stock = $(this).attr('data-stock');
        if (parseInt(qty) + 1 > stock) {
            alert("Cannot exceed stock quantity.");
            return false;
        }
        update_qty(parseInt(qty) + 1, $(this).attr('data-id'), $(this).attr('data-price'));
        $(this).parent().siblings('input[name="qty"]').val(parseInt(qty) + 1);
    })

    function update_qty(qty, id, price){
        start_load();
        $.ajax({
            url: 'admin/ajax.php?action=update_cart_qty',
            method: "POST",
            data: {id: id, qty: qty},
            success: function(resp){
                if(resp == 1){
                    var itemTotal = parseFloat(price) * qty;
                    $('button[data-id="'+id+'"]').closest('.card').find('.item-total').text(itemTotal.toFixed(2));
                    update_total_amount();
                    end_load();
                }
            }
        })
    }

    function update_total_amount() {
        var totalAmount = 0;
        $('.item-total').each(function(){
            totalAmount += parseFloat($(this).text());
        });
        var shippingAmount = parseFloat($('#shipping-amount').text());
        var totalWithShipping = totalAmount + shippingAmount;
        $('#total-amount').text(totalAmount.toFixed(2));
        $('#total-with-shipping').text(totalWithShipping.toFixed(2));
    }

    $('#checkout').click(function(){
        if('<?php echo isset($_SESSION['login_user_id']) ?>' == 1){
            location.replace("index.php?page=checkout")
        }else{
            uni_modal("Checkout","login.php?page=checkout")
        }
    })
    function update_qty(qty, id, price){
    start_load();
    $.ajax({
        url: 'admin/ajax.php?action=update_cart_qty',
        method: "POST",
        data: {id: id, qty: qty},
        success: function(resp){
            if(resp == 1){
                var itemTotal = parseFloat(price) * qty;
                $('button[data-id="'+id+'"]').closest('.card').find('.item-total').text(itemTotal.toFixed(2));
                update_total_amount();
                end_load();
                
                // Show success message using SweetAlert
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'The quantity has been updated successfully.',
                });
            } else {
                // Show error message using SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'There was an issue updating the cart. Please try again.',
                });
                end_load();
            }
        }
    });
}
$('.rem_cart').click(function(e) {
    e.preventDefault(); // Prevent the default action (the link)

    // Use SweetAlert to show a confirmation dialog
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to remove this item from your cart?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove it!',
        cancelButtonText: 'No, keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            // Proceed with the deletion
            var id = $(this).attr('data-id');
            
            $.ajax({
                url: 'admin/ajax.php', // Your PHP file that handles the deletion
                method: 'GET',
                data: {
                    action: 'delete_cart',
                    id: id
                },
                success: function(response) {
                    // Check if the response is 'success'
                    if (response.trim() === 'success') {
                        // Show SweetAlert success message and reload the page
                        Swal.fire(
                            'Deleted!',
                            'The item has been removed from your cart.',
                            'success'
                        ).then(() => {
                            location.reload(); // Reload the page after successful deletion
                        });
                    } else {
                        // Handle the error if the response is not 'success'
                        Swal.fire(
                            'Error!',
                            'There was an issue with the deletion.',
                            'error'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    // Log the error details to the console
                    console.log("Error: " + error);
                    console.log("Status: " + status);
                    console.log(xhr.responseText);
                    Swal.fire(
                        'Error!',
                        'There was an issue with the request.',
                        'error'
                    );
                }
            });
        }
    });
});

</script>