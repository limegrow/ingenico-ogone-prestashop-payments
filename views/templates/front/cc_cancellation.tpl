<div id="ingenico-error">
    <!-- <p>{* $message *}</p> -->
    <script type="application/javascript">
        window.onload = function() {
            top.jQuery("body").trigger(
                "ingenico:inline:failure",
                ["{$message nofilter}", "{$aliasId}", "{$cardBrand}", "{$iFrameUrl nofilter}"]
            );
        };
    </script>
</div>