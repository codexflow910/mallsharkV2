@extends('layouts.new-main')
@section('content')
@section('title', 'List Management')
<?php
$listingresults = array();
?>

<section class="admin-dashboard dshbrdin buyer mt-0">
    @include('admin.adminsidebar')
    <div class="dshbrdinadmin list-management">

        <div class="container">
            <div class="row">
                <div class="col-sm-12 mt-40">
                    <h3>Pools Stats</h3>
                </div>


            </div>   
            <?php
            if (count($data) > 0) {
                ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Pool Id</th>
                            <th>Pool Name</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        foreach ($data as $pool) {

                            if ($pool['pool_name'] != 'escrow') {
                                echo ' <tr>
                            <td>' . $pool['pool_id'] . '</td>
                            <td>' . $pool['pool_name'] . '</td>
                            <td>' . $pool['amount'] . '</td>
                        </tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            <?php } ?>



        </div>

    </div>

</section>

@endsection