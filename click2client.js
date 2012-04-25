var Click2Client = {
    reset : [],
    indicators : []
};

jQuery(document).ready(function() {
    jQuery('.click2client').each(function() {
        var flash_id = jQuery(this).attr('id');
        var params = {
        };
        swfobject.embedSWF(click2clientL10n.plugin_url + '/dialer.swf?exampletext='+click2clientL10n.exampletext+'&showlogo='+click2clientL10n.showlogo, flash_id, '230px', '55px', '9.0.0');
        Click2Client.indicators.push(function() {
            jQuery('#'+flash_id).get(0).UpdateStatusIndicator(true);
        });
        Click2Client.reset.push(function() {
            jQuery('#'+flash_id).get(0).SetPhoneNumberValue('');
            jQuery('#'+flash_id).get(0).UpdateStatusIndicator(false);
        });
    });
});

var StartCall = function(caller) {
    for(var id in Click2Client.indicators) {
        Click2Client.indicators[id]();
    }
    if(caller && caller.length >= 9) {
        jQuery.ajax({
            url : click2clientL10n.plugin_url + '/wp-click2client.php',
            data : {
                caller : caller
            },
            success : function(data) {
                setTimeout(function() {
                    for(var id in Click2Client.reset) {
                        Click2Client.reset[id]();
                    }
                }, 10000);
            }
        });
    }
};

