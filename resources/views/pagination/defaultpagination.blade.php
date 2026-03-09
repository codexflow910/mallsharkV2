<?php
if ($paginator->lastPage() > 1) {
    ?>

    <ul class="pagination pull-right">
        <li class="page-item <?php echo ($paginator->currentPage() == 1) ? ' disabled' : '' ?>">
            <a class="page-link" href="{{ $paginator->url($paginator->currentPage()-1) }}">Previous</a>
            <?php
            for ($i = 1; $i <= $paginator->lastPage(); $i++) {
                ?>
            <li class="page-item <?php echo ($paginator->currentPage() == $i) ? ' active' : '' ?>">
                <a class="page-link" href="<?php echo $paginator->url($i); ?>"><?php echo $i ?></a>
            </li>
        <?php } ?>

        <li class="page-item <?php echo ($paginator->currentPage() == $paginator->lastPage()) ? ' disabled' : '' ?>">
            <a class="page-link" href="<?php echo $paginator->url($paginator->currentPage() + 1) ?>" >Next</a>
        </li>
    </ul>
<?php } ?>