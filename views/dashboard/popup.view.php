<!-- Popup window with shares detail for post -->
<div id="smaasb_<?php echo $post_id ?>" style="display: none;">
    <div id="social-analytics">
    <div class="smaasb-posts smaasb-posts-popup">    
    <h3><?php echo stripslashes($post_title) ?></h3>
    <h4>Shares statistics</h4>
    <table width="100%">    
    <tr>
            <td class="total center">            
            <b>Total</b>
            <?php echo (int) $post->total; ?>
            </td>

            <td class="center">
            <i class="fa fa-facebook-square"></i>
            <?php echo (int) $post->facebook; ?>
            </td>

            <td class="center">
            <i class="fa fa-twitter-square"></i>
            <?php echo (int) $post->twitter; ?>
            </td>

            <td class="center">
            <i class="fa fa-linkedin-square"></i>
            <?php echo (int) $post->linkedin; ?>
            </td>

            <td class="center">
            <i class="fa fa-pinterest-square"></i>
            <?php echo (int) $post->pinterest; ?>
            </td>

            <td class="center">
            <i class="fa fa-google-plus-square"></i>
            <?php echo (int) $post->googleplus; ?>
            </td>
        </tr>
    </table>
    </div>
    </div>
</div>
<!-- End of: Popup window with shares detail for post -->