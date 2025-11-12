<?php
    include 'admin_header.php';
?>
<title>Historial de Pagos - Admin</title>

<style>
    .monto-pago {
        font-weight: bold;
        color: #28a745; /* Verde */
    }

    .btn-editar-pago {
        background-color: #3B82F6; /* Azul */
        color: white;
        padding: 6px 10px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 0.9em;
    }
    .btn-editar-pago:hover {
        background-color: #2563EB;
    }

    
</style>

<div class="report-section">
    <h2>Historial de Pagos</h2>
    <p>Este reporte usa la <code>v_HistorialPagos</code> para mostrar todas las transacciones.</p>
    <table>
        <thead>
            <tr>
                <th>ID Pago</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Cita (Tatuaje)</th>
                <th>Tipo de Pago</th>
                <th>Método</th>
                <th>Monto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="pagos-tabla-body">
            <tr><td colspan="7">Cargando historial de pagos...</td></tr>
        </tbody>
    </table>
</div>

<script src="js/historial-pagos.js"></script>
<script src="js/dashboard.js"></script> 

<?php
    include 'admin_footer.php';
?>