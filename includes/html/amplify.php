<style>
            body{
                font-family: 'Open Sans',sans-serif;
            }
            .relative{position: relative}
            .absolute{position: absolute}
            .row-fluid{width: 100%;clear: both}
            .pull-left{float: left}
            .pull-right{float: right}
            /*.head{height: 95px;background-color: #c93c23;padding: 35px 30px 30px;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box}*/
            .inner-box{background-color: #fff;padding :25px 0 25px 25px;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;width: 670px}
            .header{font-weight: 300;font-size: 18px;color: #585858;line-height: 1;margin-bottom: 10px}
            .small-header{font-size: 12px;line-height: 1;color: #a1a1a1}
            .api-box{background-color: #fafafa;padding: 20px 38px 20px 25px;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;margin-bottom: 20px;float: left}
            .api-box .control-label{font-size: 22px;font-weight: 300;line-height: 1;width: 180px;margin-top: 10px;color: #3d3d3d}
            .api-box input[type='text']{background-color: #fff;border: 1px solid #e7e6e6;height: 40px;width: 400px;}
            .api-box input[type='text']:focus{outline: none;outline-offset: 0}
            .api-box .control-group{margin-bottom: 35px}
            .warning{color: #993300;font-size: 14px;font-weight: 400;margin: 0;width: 50%;display: inline-block;padding-left: 50px;}
            .btn1{background-color: #e7543c;border-radius: 5px;color: #fff;font-size: 18px;font-weight: 600;padding: 10px 30px;border-style: none;}
</style>
        <div class='row-fluid'>
            <div class='pull-left'><img src=<?php echo plugins_url('images/amp-connect.png', dirname(dirname(__FILE__))); ?>></div>
        </div>
        <div class='inner-box row-fluid'>
            <div class='header'>To enable Amplify, you need to have an account at <a href='http://getamplify.com' style="color: #c93c23;text-decoration: none;">Getamplify.com</a> and get your free API and secret for this site</div>
            <div class='small-header' style="margin-bottom: 25px">Average time to signup, opening an account entering API key and secret takes about 27 seconds. :)</div>
            <div class='row-fluid pull-left' style="margin-bottom: 8px"><div class='pull-right small-header'>Change API key</div></div>
            <form action="">
            <div class='row-fluid api-box'>
                <div class='row-fluid control-group pull-left'>
                    <div class='pull-left control-label'>API Key</div>
                    <div class='pull-left'><input type='text' id="input_apikey" value="<?php echo get_option('_AMPLIFY_API_KEY', false);?>"/></div>
                </div>
                <div class='row-fluid control-group pull-left'>
                    <div class='pull-left control-label'>API Secret</div>
                    <div class='pull-left'><input type='text'  id="input_apisecret" value="<?php echo get_option('_AMPLIFY_API_SECRET', false);?>"/></div>
                </div>
                <div class='row-fluid control-group pull-left'>
                    <div class='pull-left control-label'>Project ID</div>
                    <div class='pull-left'><input type='text' id="input_projectid" value="<?php echo get_option('_AMPLIFY_PROJECT_ID', false);?>"/></div>
                     <span class="warning" id="fail" style="display:none"><i>* Please check your API key and Sercet</i></span>
                    <span class="warning" id="success" style="display:none"><i>* key save successfully</i></span>
                </div>
                <div class='row-fluid pull-right relative'>
                    <div class='pull-right'> <input type="button" id="save_btn" name="submit" value="SAVE" class="btn1"/></div>
                    <div class='pull-right'><a href='' class='absolute' style="text-decoration: none;color: #aeaeae;font-weight: 300;font-size: 12px;bottom: 0;right: 22%">Cancel</a></div>
                </div>
            </div>
            </form>
            
            <div class='row-fluid'>
                <div class='pull-right' style="font-size: 18px;font-weight: 300;color: #585858">You can also mail us at <a href='mailto:support@betaout.com' style="color: #c93a23;text-decoration: none;">support@betaout.com</a></div>
            </div>
        </div>


    <script type="text/javascript" >

jQuery(document).ready(function($) {
      $('#save_btn').click(function() {
        var apikey=$("#input_apikey").val();
        var seckey=$("#input_apisecret").val();
        var projectId=$("#input_projectid").val();
	var data = {
		action: 'verify_key',
		amplifyApiKey:apikey,
                amplifyApiSecret:seckey,
                amplifyProjectId:projectId
	};

     $.post(ajaxurl, data, function(response) {
         if(response.responseCode=="200"){
          $("#success").show();
           $("#fail").hide();
          }else{
          $("#fail").show();

          }
	},"json");
        });
});
</script>