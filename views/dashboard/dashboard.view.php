<!-- Header area -->
<h1>Social Analytics Dashboard</h1>

<div style="text-align: right;">
<div style="padding-bottom: 2px;">Analyze posts from:</div>
<input id="date_range" name="date_range">
</div>

<div style="clear: both"></div>
<!-- End of: Header area -->

<h2>Please select a date range to the right to display your stats.</h2>

<!-- Table area -->
<div class="smaasb-posts">

    <table>
    <thead>
    <tr>
        <td colspan="2"><h3>Top shared posts</h3></td>
        <td class="center">Total</td>
        <td class="center"><i class="fa fa-facebook-square"></i></td>
        <td class="center"><i class="fa fa-twitter-square"></i></td>
        <td class="center"><i class="fa fa-linkedin-square"></i></td>
        <td class="center"><i class="fa fa-pinterest-square"></i></td>
        <td class="center"><i class="fa fa-google-plus-square"></i></td>
        <td class="center efficiency">Efficiency</td>
    </tr>
    </thead>
    <?php if (isset($data) && is_array($data) && count($data) > 0 ) : ?>
    <?php foreach ($data as $post): ?> 
        <tr>
            <td class="thumb"><?php echo ($post->thumb) ? $post->thumb : '<span><i class="fa fa-camera"></i></span>' ?></td>
            
            <td class="title">
                <a href="<?php echo $post->url ?>" target="new" class="name"><?php echo $post->title ?></a>
            </td>

            <td class="total center">
            <?php echo (int) $post->total; ?>
            </td>

            <td class="center">
            <?php format_shares_number($post->facebook); ?>
            </td>

            <td class="center">
            <?php format_shares_number($post->twitter); ?>
            </td>

            <td class="center">
            <?php format_shares_number($post->linkedin); ?>
            </td>

            <td class="center">
            <?php format_shares_number($post->pinterest); ?>
            </td>

            <td class="center">
            <?php format_shares_number($post->googleplus); ?>
            </td>
                   
            <td class="line efficiency">
                <div class="bar">
                    <span style="width: <?=round(100 * $post->total / $data[0]->total)?>%;"></span>
                </div>             
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if  (count($data) > 15 ) : ?>
    <tfoot>
    <tr>
        <td colspan="2"><h3>&nbsp;</h3></td>
        <td class="center">Total</td>
        <td class="center"><i class="fa fa-facebook-square"></i></td>
        <td class="center"><i class="fa fa-twitter-square"></i></td>
        <td class="center"><i class="fa fa-linkedin-square"></i></td>
        <td class="center"><i class="fa fa-pinterest-square"></i></td>
        <td class="center"><i class="fa fa-google-plus-square"></i></td>
        <td class="center efficiency">Efficiency</td>
    </tr>
    </tfoot>
    <?php endif ?>

<?php else : ?>

<tr>
<td colspan="10">
No data for selected period.
</td>
</tr>

<?php endif ?>
</table>

</div>

<?php if (isset($data) && is_array($data) && count($data) > 0 ) : ?>
<div>
<p style="text-align:right">
<a href="edit.php?page=social-analytics&export=1&start=<?php echo $start->format('Y-m-d') ?>&end=<?php echo $end->format('Y-m-d') ?>">Download CSV</a>
</p>
</div>
<?php endif ?>


<!-- End of: Table area -->

<?php
// Format total shares number
function format_shares_number($shares)
{
    $shares = (int) $shares;
    echo ($shares != 0 ) ? $shares : '<span class="zero">-</span>';   
}
?>

<script>
// Initialize custom date range picker
jQuery(document).on('ready', function($) {
    var opened = false;

    // Reload page with new date settings
    var date_range_changed = function(range) {
        window.location.search = '?page=social-analytics&start='+range.start+'&end='+range.end;
    }

    // Initialize date range picker
    jQuery("#date_range").daterangepicker({
        datepickerOptions : {
            numberOfMonths : 2,
        },
        initialText : 'Select period...',     
        onOpen: function() { opened = true; },
        onChange: function() { if (opened) date_range_changed(JSON.parse(jQuery('#date_range').val())); }
    });

    // Set currently seleected dates, based on settings coming from backend
    jQuery("#date_range").daterangepicker("setRange", {
        start: moment('<?php echo $start->format('Y-m-d') ?>').toDate(),
        end: moment('<?php echo $end->format('Y-m-d') ?>').toDate()
    })
});
</script>
