<div class="panel panel-default">
    <div class="panel-body">

        <p ng-repeat="fieldtype in fieldtypes">
                <a href="" class="btn btn-primary btn-block"
                           ng-click="addField(fieldtype.name)">@{{ fieldtype.label }}</a>
        </p>

    </div>
</div>