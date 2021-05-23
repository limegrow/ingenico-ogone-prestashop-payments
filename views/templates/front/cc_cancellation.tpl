<div id="ingenico-error">
    <!-- <p>{* $message *}</p> -->
    <script type="application/javascript">
        window.onload = function() {
            top.jQuery("body").trigger(
                "ingenico:inline:failure",
                ["{$message nofilter}", "{$aliasId|escape}", "{$cardBrand|escape}", "{$iFrameUrl nofilter}"]
            );
        };
    </script>
</div>