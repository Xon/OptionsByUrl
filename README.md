# XenForo2-OptionsByUrl

Allows overriding XF options via URL. Unauthenticated.

# Usage 
Prefixing `xf.options.` to the option name;

ie;
`http://example.org/?xf.options.boardActive=0`

Array values should be encoded as json;
ie;
`http://example.org/?xf.options.tosUrl={"type":"default"}`

# Warnings

Only runs when development mode is enabled, logs an error otherwise.

A site with this add-on installed should not be internet accessible as this allows guest/non-admin access to changing core XenForo settings which may result in arbitrary code execution