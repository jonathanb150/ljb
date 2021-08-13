<?php require('php/dom_elements/head.php'); ?>
<?php 
if (isset($_POST['delete_logs'])) {
    recursiveRemoveDirectoryContents(LOGS_PATH);
    die();
}
else if (isset($_POST['delete_log']) && isset($_POST['log_name'])) {
    unlink(LOGS_PATH.$_POST['log_name']);
    die();
}
// Get logs
$logs = glob(LOGS_PATH."*");

if(is_array($logs) && count($logs) > 0) {    
    echo '<div class="text-center"><button style="margin: 0.5em 0;" type="button" class="btn btn-danger btn-lg" data-toggle="modal" data-target="#delete_logs" st>Delete All Logs</button></div>';
    echo '<div class="modal fade" id="delete_logs" tabindex="-1"aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title">Delete Logs</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                Are you sure you want to delete all logs?
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                <button type="button" class="btn btn-primary">Yes</button>
                </div>
            </div>
            </div>
        </div>';
    echo '<div class="container-sm" style="margin-top: 1em">';
    echo '<nav><div class="nav nav-pills nav-fill" id="logs-tabs" role="tablist">';
    for ($i=0; $i < count($logs); $i++) { 
        echo '<a href="#log'.($i+1).'" '.($i == 0 ? 'class="nav-link active"' : 'class="nav-link"').' id="nav-log'.($i+1).'-tab" data-toggle="tab" role="tab" aria-controls="nav-log'.($i+1).'" aria-selected="true">'.basename($logs[$i]).(stringInVariable('error', strtolower(file_get_contents($logs[$i]))) ? '<i class="fas fa-exclamation-circle" style="color:yellow; margin-left: .25em;"></i>' : '');
        echo '<button log-name="'.basename($logs[$i]).'" type="button" class="btn btn-danger del-log" style="margin-left: 0.5em; opacity: '.($i == 0 ? '1' : '0.5').'"><i class="fas fa-trash"></i></button></a>';
    }
    echo '</div></nav>';
    
    echo '<div class="tab-content" id="nav-tabContent" style="margin-top: 1em; max-height: 40em; overflow: auto">';
    for ($i=0; $i < count($logs); $i++) {
        echo '<div style="font-family: Lucida Console; background: #555d66; color: rgba(255,255,255,.5); padding: 1em;" id="log'.($i+1).'" '.($i == 0 ? 'class="tab-pane fade show active card"' : 'class="tab-pane fade card"').' role="tabpanel" aria-labelledby="nav-log'.($i+1).'-tab">'.nl2br(file_get_contents($logs[$i])).'</div>';
    }
    echo '</div>';
    echo '</div>';
} else {
    echo '<div class="jumbotron jumbotron-fluid" style="background: #555d66;">
            <div class="container">
            <h1 class="display-4">Ooops! <b>404</b></h1>
            <p class="lead">There are no logs yet to show.</p>
            <a class="btn btn-primary btn-lg" href="/admin" role="button">Go back to Home</a>
            <a class="btn btn-primary btn-lg" href="'.$_SERVER['SCRIPT_NAME'].'" role="button">Try again</a>
            </div>
        </div>';
}
?>
<div class="modal fade" id="delete_log" tabindex="-1"aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title">Delete Log</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        </div>
        <div class="modal-body">
        Are you sure you want to delete this log?
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
        <button type="button" class="btn btn-primary">Yes</button>
        </div>
    </div>
    </div>
</div>
<?php require('php/dom_elements/footer.php'); ?>
