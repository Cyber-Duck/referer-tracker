<br><br>
<hr>
<div style="font-size:14px;color:#666666;">
    <strong>IP address</strong>: <?php echo $referal->Ip ?>
    <br><br><strong>Referer</strong>:
    <ol>
        <?php foreach ($referal->List as $row): ?>
        <li>
            <a href="<?php echo $row->Url ?>">
                <?php echo $row->Text ?>
            </a> <small style="color:#666;">(<?php echo $row->Date ?>)</small>
        </li>
        <?php endforeach; ?>
    </ol>
</div>