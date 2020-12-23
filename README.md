# XenForo2-OptionsByUrl

Allows overriding XF options via URL. Unauthenticated.

# Usage 
Prefix `xf.options.` to the option name;

ie;
`http://example.org/?xf.options.boardActive=0`

Array values should be encoded as json;
ie;
`http://example.org/?xf.options.tosUrl={"type":"default"}`

# Warnings

Only runs when development mode is enabled, logs an error otherwise.

A site with this add-on installed should not be internet accessible as this allows guest/non-admin access to changing core XenForo settings which may result in arbitrary code execution

# Installing

```
git clone https://github.com/Xon/OptionsByUrl.git src/addons/SV/OptionsByUrl
php cmd.php xf-addon:install SV/OptionsByUrl
```