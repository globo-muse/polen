<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"
<div class="container-fluid">
    <div class="panel panel-primary">
        <div class="panel-heading clearfix d-flex">
            <div class="col-md-10"><h3>Registro do formulário Ajuda</h3></div>
<!--            <div class="col-md-2">-->
<!--                <a href="#" class="btn btn-success pull-right float-right" id="export">Exportar CSV</a>-->
<!--            </div>-->
        </div>
        <div class="table-responsive-lg panel-body">
            <table id="list-table" class="table table-striped table-bordered hover">
                <thead>
                <tr>
                    <th class="text-center">Nome</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Telefone</th>
                    <th class="text-center">Mensagem</th>
                    <th class="text-center">Cadastro</th>
                </tr>
                </thead>
                <tfoot>
                <tr class="text-center">
                    <th class="text-center">Nome</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Telefone</th>
                    <th class="text-center">Mensagem</th>
                    <th class="text-center">Cadastro</th>
                </tr>
                </tfoot>
                <tbody>
                <?php foreach ($leads as $lead) : ?>
                    <tr class="text-center">
                        <td><?php echo $lead->name; ?></td>
                        <td><?php echo $lead->email; ?></td>
                        <td><?php echo $lead->phone; ?></td>
                        <td><?php echo $lead->message; ?></td>
                        <td><?php echo date("d/m/Y H:i:s", strtotime($lead->created_at)); ; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready( function ($) {
        $('#list-table').DataTable({
            // "pageLength": 5,
            // "pagingType": "full_numbers"
        });
        $('#list-table').removeClass( 'display' ).addClass('table table-striped table-bordered');
    });

</script>