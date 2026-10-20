<div class="panel"><div class="panel-head"><h2>Generate Report</h2></div><div class="panel-body pad"><form method="post" action="index.php"><input type="hidden" name="action" value="generate_report"><input type="hidden" name="page" value="reports"><div class="grid-2"><div class="field"><label>Report Type</label><select name="report_type"><option>Weekly Summary</option><option>Monthly Summary</option><option>Annual Summary</option></select></div><div class="field"><label>Date Range Start</label><input type="date" name="date_range_start" value="<?= BedsHelper::e(
    date("Y-m-d", strtotime("-30 days")),
) ?>" required></div><div class="field"><label>Date Range End</label><input type="date" name="date_range_end" value="<?= BedsHelper::e(
    BedsHelper::today(),
) ?>" required></div><div class="field" style="display:flex;align-items:flex-end;"><button class="btn btn-primary" style="width:100%;" type="submit">Generate Report</button></div></div></form></div></div>
<div class="panel"><div class="panel-head"><h2>Report History</h2></div><div class="panel-body"><?php if (!$db["reports"]): ?><?= BedsHelper::emptyState(
        "No reports generated yet.",
        "Generate your first report above.",
    ) ?><?php else: ?><?php
    $reports = $db["reports"];
    usort(
        $reports,
        static fn(array $a, array $b): int => strcmp(
            $b["date_generated"],
            $a["date_generated"],
        ),
    );
    ?><table><thead><tr><th>ID</th><th>Type</th><th>Range</th><th>Total Incidents</th><th>Avg Response</th><th>Generated</th><th></th></tr></thead><tbody><?php foreach (
    $reports
    as $row
): ?><tr><td class="mono">#<?= str_pad(
    (string) $row["report_id"],
    3,
    "0",
    STR_PAD_LEFT,
) ?></td><td><?= BedsHelper::e(
    $row["report_type"],
) ?></td><td class="mono text-muted"><?= BedsHelper::e(
    $row["date_range_start"],
) ?> → <?= BedsHelper::e($row["date_range_end"]) ?></td><td><?= (int) $row[
    "total_incidents"
] ?></td><td><?= BedsHelper::e(
    $row["avg_response_time"],
) ?></td><td class="mono text-muted"><?= BedsHelper::e(
    $row["date_generated"],
) ?></td><td><a class="btn btn-sm btn-ghost" href="<?= BedsHelper::e(
    BedsHelper::redirect("reports", [
        "modal" => "report",
        "id" => $row["report_id"],
    ]),
) ?>">Preview</a></td></tr><?php endforeach; ?></tbody></table><?php
endif; ?></div></div>
