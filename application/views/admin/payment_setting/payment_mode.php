<?php
$all_modes    = $this->config->item('all_payment_modes');
$active_modes = $this->config->item('payment_mode');
?>

<div class="row payment-settings-row">
    <?php $this->load->view('setting/sidebar'); ?>

    <!-- Center card: title + add new mode -->
    <div class="col-md-8 payment-methods-col">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title titlefix">Payment Mode</h3>
            </div>
            <div class="p-4">
                <div class="gw-tab-card">
                    <div class="cred-header">
                        <div class="cred-header-icon"><i class="fa fa-plus"></i></div>
                        <h6>Add New Payment Mode</h6>
                    </div>
                    <div class="cred-body">
                        <div class="cred-field cred-field-top">
                            <label>New Mode Name</label>
                            <div class="inp-wrap d-flex" style="gap:10px;">
                                <input type="text" id="new_payment_mode_input" class="inp" placeholder="e.g., GPay, EVC Plus" style="flex:1;">
                                <?php if ($this->rbac->hasPrivilege('payment_mode', 'can_edit')) : ?>
                                <button type="button" id="btn_add_payment_mode" class="btn btn-info btn-sm" style="white-space:nowrap;">
                                    <i class="fa fa-plus-circle"></i> Add Mode
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="cred-header mt-2">
                        <div class="cred-header-icon"><i class="fa fa-info-circle"></i></div>
                        <h6>How it works</h6>
                    </div>
                    <div class="cred-body">
                        <p style="color:#777; font-size:13px; line-height:1.8;">
                            Use the <strong>toggle switches</strong> in the <em>Payment Modes</em> panel on the right to <strong>enable</strong> or <strong>disable</strong> each payment mode.<br>
                            Disabled modes will <strong>not appear</strong> in any payment dropdowns across the application.<br>
                            You can also <strong>add a new custom mode</strong> using the field above.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /.col-md-8 -->

    <!-- Right sidebar: Payment Modes with toggle switches -->
    <div class="col-md-2 gateway-selector-col">
        <div class="gw-selector-card card">
            <div class="cred-header">
                <div class="cred-header-icon"><i class="fa fa-exchange"></i></div>
                <h6>Payment Modes</h6>
            </div>
            <div class="gw-selector-list" style="padding: 8px 0;">
                <?php if (is_array($all_modes)) : foreach ($all_modes as $key => $mode) :
                    $is_active = (is_array($active_modes) && array_key_exists($key, $active_modes));
                ?>
                <div class="pm-sidebar-item d-flex align-items-center justify-content-between" style="padding: 7px 12px; border-bottom: 1px solid #f5f5f5;">
                    <span class="gw-selector-name" style="font-size:13px; color:#333;"><?php echo htmlspecialchars($mode); ?></span>
                    <?php if ($this->rbac->hasPrivilege('payment_mode', 'can_edit')) : ?>
                    <label class="switch-toggle mb-0">
                        <input type="checkbox" class="pm-toggle-switch" data-key="<?php echo htmlspecialchars($key); ?>" <?php echo $is_active ? 'checked' : ''; ?>>
                        <span class="switch-slider"></span>
                    </label>
                    <?php else : ?>
                    <span class="badge <?php echo $is_active ? 'badge-success' : 'badge-secondary'; ?>" style="font-size:10px;"><?php echo $is_active ? 'On' : 'Off'; ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div><!-- /.col-md-2 -->

</div><!-- /.row -->

<style>
/* Toggle Switch */
.switch-toggle {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 22px;
    flex-shrink: 0;
}
.switch-toggle input { opacity: 0; width: 0; height: 0; }
.switch-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #ccc;
    border-radius: 22px;
    transition: .3s;
}
.switch-slider:before {
    position: absolute;
    content: "";
    height: 16px; width: 16px;
    left: 3px; bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: .3s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.switch-toggle input:checked + .switch-slider { background-color: #00a65a; }
.switch-toggle input:checked + .switch-slider:before { transform: translateX(18px); }
.switch-toggle input:disabled + .switch-slider { opacity: 0.5; cursor: not-allowed; }

.pm-sidebar-item:last-child { border-bottom: none !important; }
</style>

<script type="text/javascript">
$(document).ready(function () {

    var saveUrl = "<?php echo site_url('admin/paymentsettings/toggle_payment_mode'); ?>";

    // Toggle each switch individually via AJAX
    $(document).on('change', '.pm-toggle-switch', function () {
        var $cb  = $(this);
        var key  = $cb.data('key');
        var on   = $cb.is(':checked') ? 1 : 0;

        $cb.prop('disabled', true);

        $.ajax({
            url: saveUrl,
            type: 'POST',
            data: { mode_key: key, enabled: on },
            dataType: 'json',
            success: function (data) {
                if (data.st === 0) {
                    successMsg(data.msg);
                } else {
                    errorMsg(data.msg);
                    $cb.prop('checked', !on); // revert
                }
            },
            error: function () {
                errorMsg('Error saving. Please try again.');
                $cb.prop('checked', !on);
            },
            complete: function () {
                $cb.prop('disabled', false);
            }
        });
    });

    // Add new payment mode
    $('#btn_add_payment_mode').on('click', function () {
        var newMode = $.trim($('#new_payment_mode_input').val());
        if (!newMode) {
            errorMsg('Please enter a payment mode name.');
            return;
        }
        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: "<?php echo site_url('admin/paymentsettings/add_payment_mode'); ?>",
            type: 'POST',
            data: { new_payment_mode: newMode },
            dataType: 'json',
            success: function (data) {
                if (data.st === 0) {
                    successMsg(data.msg);
                    setTimeout(function () { window.location.reload(); }, 1000);
                } else {
                    errorMsg(data.msg);
                }
            },
            error: function () {
                errorMsg('Error saving. Please try again.');
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

});
</script>
