<?php $currency_symbol = $this->customlib->getHospitalCurrencyFormat();
$title = 'Monthly/Yearly Income Statement Report'; ?>
<div class="row"><div class="col-md-12"><div class="card">
    <?php $this->load->view('admin/report/_finance'); ?>
    <div class="card-header"><h3 class="card-title"><?php echo $title; ?></h3></div>
    <div class="card-body">
        <form action="<?php echo base_url('admin/report/incomestatementreport'); ?>" method="post" accept-charset="utf-8">
            <?php echo $this->customlib->getCSRF(); ?>
            <div class="row align-items-end">
                <div class="col-sm-4"><div class="form-group">
                    <label for="group_by">Group by Date</label>
                    <select id="group_by" name="group_by" class="form-control">
                        <option value="monthly" <?php echo $group_by === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                        <option value="yearly" <?php echo $group_by === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                    </select>
                </div></div>
                <div class="col-sm-2"><div class="form-group">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                </div></div>
            </div>
        </form>
        <div class="download_label"><?php echo $title . ' - ' . ($group_by === 'yearly' ? 'Yearly' : 'Monthly'); ?></div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover example" data-export-black-border="true" data-export-title="<?php echo $title; ?>">
                <thead><tr>
                    <th>Group by Date</th>
                    <th><?php echo $this->lang->line('type'); ?></th>
                    <th class="text-end"><?php echo $this->lang->line('income_in') . ' (' . $currency_symbol . ')'; ?></th>
                    <th class="text-end"><?php echo $this->lang->line('expense_out') . ' (' . $currency_symbol . ')'; ?></th>
                    <th class="text-end"><?php echo $this->lang->line('overall_balance') . ' (' . $currency_symbol . ')'; ?></th>
                </tr></thead>
                <tbody>
                <?php $total_income = 0; $total_expense = 0;
                foreach ($report_data as $row) {
                    $income = (float)$row['income_in'];
                    $expense = (float)$row['expense_out'];
                    $balance = $income - $expense;
                    $total_income += $income;
                    $total_expense += $expense;
                    $timestamp = strtotime($row['record_date']);
                    $period = $timestamp ? date($group_by === 'yearly' ? 'Y' : 'F, Y', $timestamp) : '-';
                ?>
                <tr>
                    <td data-order="<?php echo html_escape($row['record_date']); ?>"><?php echo html_escape($period); ?></td>
                    <td><?php echo html_escape($row['inc_exp_head']); ?></td>
                    <td class="text-end"><?php echo amountFormat($income); ?></td>
                    <td class="text-end"><?php echo amountFormat($expense); ?></td>
                    <td class="text-end"><span class="<?php echo $balance < 0 ? 'text-danger' : ''; ?>"><?php echo amountFormat($balance); ?></span></td>
                </tr>
                <?php } ?>
                </tbody>
                <?php if (!empty($report_data)) { $net_balance = $total_income - $total_expense; ?>
                <tfoot><tr>
                    <th><?php echo $this->lang->line('total_amount'); ?></th><th></th>
                    <th class="text-end"><?php echo $currency_symbol . amountFormat($total_income); ?></th>
                    <th class="text-end"><?php echo $currency_symbol . amountFormat($total_expense); ?></th>
                    <th class="text-end"><span class="<?php echo $net_balance < 0 ? 'text-danger' : ''; ?>"><?php echo $currency_symbol . amountFormat($net_balance); ?></span></th>
                </tr></tfoot>
                <?php } ?>
            </table>
        </div>
    </div>
</div></div></div>
