# Changelog

All notable changes to this project will be documented in this file.

## v1.22.0 - 2025-01-23
### Added
- Added `full` option for `group_flush` configuration option

### Changed
- Switch `group_flush` default value to `scan`
- Swap all `error_log()` call for internal `log()` helper
- Log all messages when `debug` mode is enabled
- Improve performance of analytics API requests

### Fixed
- Fixed rare `TypeError` in `Diagnostics::redisVersion()`
- Fixed scheme detection for `wp redis cli` command
- Fixed inverted multi/pipeline modes
- Fixed OpenTelemetry tracer integration
- Avoid cloning connection when computing metrics

### Removed
- Removed deprecated `$RedisCachePro` global

## v1.21.3 - 2024-10-29
### Changed
- Allow more cache key separators in Relay prefix health check

### Fixed
- Fixed changelog not being accessible when `DISALLOW_FILE_MODS` is set
- Avoid rare notice when using Query Monitor
- Removed debug call

## v1.21.2 - 2024-07-18
### Added
- Support setting `strict` mode using `OBJECTCACHE_STRICT` environment variable

### Changed
- Don't enable `strict` mode when a `prefix` is set

### Fixed
- Fixed connection error when no Sentinel replicas are available

## v1.21.1 - 2024-05-21
### Fixed
- Fixed rare type mismatch when running admin lifecycle

## v1.21.0 - 2024-05-08
### Added
- Added Redis cluster support when using Relay
- Added `ErrorLogContextLogger` for easier debugging

### Changed
- Enabled `strict` mode by default
- Support `WC_SESSION_CACHE_GROUP` constant
- Use decorrelated jitter backoff algorithm for connection attempts
- Check `wp_is_file_mod_allowed('object_cache_dropin')` before automatically deleting drop-in
- Added `Relay*Connection::keysInMemory()` helpers
- Improved latency widget content when using cluster connection
- Use configured logger and log levels when logging from plugin itself
- Resolved PHP 8.4 deprecation notices

### Fixed
- Avoid rare fatal error in `Configuration::safelyFrom()`
- Fixed `objectcache_allow_dropin_mod` filter

## v1.20.2 - 2023-10-28
### Fixed
- Fixed plugin version string

## v1.20.1 - 2023-10-27
### Changed
- Log exception message when cache reset fails
- Don't run filesystem health check if `DISALLOW_FILE_MODS` is `true`
- Changed filesystem test file name from `.object-cache-test.tmp` to `object-cache.tmp`

### Fixed
- Fixed `async_flush` when using PhpRedis v6.0
- Avoid `TypeError` when using `WP_Error::get_error_data()`

## v1.20.0 - 2023-09-19
### Added
- Added cache integrity protection
- Added analytics charts to dashboard widget
- Added `objectcache_widget_metrics` filter
- Added `wp redis reset` command
- Added support for `OBJECTCACHE_OVERRIDE` and `OBJECTCACHE_MERGE` constants
- Added support for PhpRedis v6.0
- Added `analytics.sample_rate` configuration option

### Changed
- Reduce default `retries` from `5` to `3`
- Refresh nonce using `X-WP-Nonce` header
- Expose Relay license issues in Dashboard widget
- Refactored various flush mechanisms
- Expanded `ObjectCache::withoutMutations()` callback type from `Closure` to `callable`
- Delete `objectcache_*` site options when uninstalling
- Improved license API error handling

### Fixed
- Fixed failover when using Redis Sentinel
- Bump `plugins_api` priority to prevent plugins from hijacking responses
- Mute rare `undefined array key` notice in `flushBacktraceSummary()`

### Removed
- Removed `split_the_query` filter in favor of native WordPress 6.4 behavior
- Removed deprecated `rediscache_*` filters
- Removed deprecated `ObjectCache::flushMemory()` method
- Removed `objectcache_analytics_retention` filter
- Removed `--async` option from `wp redis flush`

## v1.19.0 - 2023-07-21
### Added
- Added `strict` configuration option
- Added group flush log widget
- Added link to settings to dashboard widget
- Added Relay memory health check
- Added support for `OBJECTCACHE_PREFER_RELAY` environment variable
- Added support for `path` and `socket` configuration options
- Added support for `redis-error.php` template
- Added `objectcache_cleanup_transients` filter
- Added `--skip-transients` option to `wp redis enable`

### Changed
- Render connection exceptions similar to WordPress database errors
- Throw exception when selecting non-existent database
- Delete all transients from database after enabling cache
- Support logging early flushes before plugin code was initialized
- Improved `WP_Query::get_posts()` performance using `split_the_query`
- Made Relay diagnostics more helpful
- Renamed `relay-memory-active` to `relay-memory-used`
- Not longer recommend disabling `prefetch` and `split_alloptions` when using Relay

### Fixed
- Display Relay's memory consumption more accurately
- Improve handling non-fatal `MGET` failures
- Prevent `wp_cache_init()` initializing twice
- Avoid rare fatal error when parsing JSON responses
- Avoid confusing hiredis error message Relay connection times out
- Avoid fatal error when `gmt_offset` is not valid

## v1.18.2 - 2023-04-04
### Added
- Added Query Monitor constants to panel

### Changed
- Renamed `ms-cache-median` to `ms-cache-avg`

### Fixed
- Discard transactions if they fail
- Prevent REST API responses from being cached
- Fixed measuring analytics writes
- Fixed `storeWaitAverage` calculation
- Fixed supported Redis version indicator when using Relay

## v1.18.1 - 2023-03-24
### Changed
- Updated End-User License Agreement
- Don't flush cache when updating drop-in

### Fixed
- Fixed cache timing metric
- Fixed widget flush button label on multisite
- Fixed flushing groups that end with blog id on multisite

## v1.18.0 - 2023-02-20
### Added
- Added `wp redis flush-group` CLI command

### Changed
- Reduced overall memory usage
- Made cache key generation slightly faster
- Enable flush logging by default
- Log `warning` level by default
- Execute not split `alloptions` reads on primary node
- Refactored object cache metrics to use new `ObjectCacheMetrics`
- Enabled flush logging when `save_commands` is set
- Disabled command logging entirely unless `save_commands` is set
- Return primary node from `*SentinelsConnection::client()` instead of sentinel client

### Fixed
- Handle foreign keys without a group in groups REST API endpoint
- Respect `async_flush` when using `wp_cache_delete()`

### Removed
- Dropped support for Query Monitor <=3.8.0 backtraces
- Removed metrics keys from `info()` in favor of `metrics()`
- Removed `backtrace_summary` logging and deprecated `BacktraceLogger`

## v1.17.1 - 2023-01-28
### Changed
- Switched to faster Zstandard compression level
- Handle non `int|string` cache keys stricter and more gracefully
- Throw exception for invalid cache keys when `debug` option is enabled
- Don't let the perflab plugin hijack the object cache drop-in
- Defer registering `wp redis` CLI command

### Fixed
- Fixed several multisite blog flushing issues
- Fixed accessing client on replicated connections
- Fixed setting `analytics` option to boolean
- Fixed calling `get_plugin_data()` on non-existent drop-in

## v1.17.0 - 2022-11-23
### Added
- Added support replicated and sentinel Relay connections
- Added `group_flush` configuration option supporting `keys` (new faster default), `scan` (previous default) and `incremental` (enormous datasets)
- Added `tracer` configuration option supporting `new-relic` and global `open-telemetry` tracer providers
- Support `network_flush` option when using Redis Cluster
- Support New Relic tracing for Redis Cluster, Redis Sentinel and Relay clients
- Added `*Connection::listKeys()` helpers
- Added `PhpRedisClusterConnection::eval*()` helpers
- Added Redis Server version health checks and indicators

### Changed
- Bumped scan and batch removal from `100` to `500` keys
- Flush more prudently even on main site when using `network_flush`
- Indicate whether the network or site will be flushed in overview widget in multisite environments
- Renamed all the things from _master(s)_ to _primary/primaries_ with backwards compatibility
- Improved must-use plugin detection
- ⚠️ Added `client()` method to `ConnectionInterface`
- ⚠️ Use new `ClientInterface` in all `ConnectionInterface` implementations
- ⚠️ Renamed `Connector` interface to `ConnectorInterface`
- ⚠️ Renamed `handleBlogFlush()` to `shouldFlushBlog()`
- Renamed `flush_network` option to `network_flush`
- Use `ObjectCache::$blogId` instead of `get_current_blog_id()`
- Use `ObjectCacheCollector::get_storage(): QM_Data` return type
- Show received bytes _and sent bytes_ in Query Monitor
- Strip slashes from commands in Query Monitor
- Improved support for `scan()` and `*scan()` methods
- Delete FOSS drop-in upon plugin activation

### Fixed
- Fixed various Redis Sentinel issues
- Fixed `Invalid regular expression` in Safari
- Fixed parsing `rawCommand()` in Query Monitor calls when using Redis Cluster
- Fixed health check for configuration constant being defined too late
- Fixed attempted property access on null in `Diagnostics::relayHasCache()`
- Fixed groups widget padding
- Fixed inverted "Relay Cache" metadata in Query Monitor

### Removed
- Dropped Twemproxy support
- Dropped `PhpRedisClusterConnection::scanNode()` helper
- Dropped `*ObjectCache::Client` constants
- Dropped `WP_REDIS_PHPREDIS_OPTIONS` constant

## v1.16.4 - 2022-10-31
### Added
- Added support for WordPress 6.1's `wp_cache_supports()`

### Changed
- Refer to "external cache" as "datastore" everywhere
- Hide `wp_cache_get_last_changed()` and `wp_cache_flush_group()` in Query Monitor

### Fixed
- Fixed inverted "Relay Cache" diagnostic
- Fixed rare undefined `OPT_MAX_RETRIES` constant error
- Don't use WordPress 5.3's `WP_REST_Request::has_param()`

## v1.16.3 - 2022-10-09
### Added
- Added new `wp redis status` command
- Added `objectcache_omit_settings_pointer` filter
- Added `relay.cache` configuration option

### Changed
- Improved support for Relay's "client only" mode
- Renamed `wp redis info` to `wp redis diagnostics`
- Made `wp redis info` an alias of `wp redis status`
- Refined hosting provider detection
- Use new `Connection::$class` property for FQCNs

### Fixed
- Fixed rare fatal `ValueError`

### Removed
- Removed no longer needed `Relay::dispatchEvents()` calls

## v1.16.2 - 2022-09-29
### Added
- Added support for Relay's "client only" mode
- Added compatibility with new Query Monitor data classes
- Added `objectcache_allow_dropin_mod` filter
- Added `relay.allowed` and `relay.ignored` configuration options

### Changed
- Append context to `objectcache_*_error` errors
- Verify `Transaction` result type and count
- Make it easy to copy all cache groups to the clipboard
- Refined API health check and diagnostics
- Refined overview widget
- Refined analytics charts loading and messages
- Refined hosting provider detection

### Fixed
- Avoid executing empty transactions
- Fixed update channel field availability
- Fixed Query Monitor request time ratio calculation
- Fixed rare Query Monitor issue when connection is `null`
- Hide more update notices when `updates` is `false`
- Don't inline widget CSS twice

## v1.16.1 - 2022-08-29
### Fixed
- Fixed health check condition

## v1.16.0 - 2022-08-22
### Added
- Added WordPress 6.1 `wp_cache_flush_group()` support
- Added REST API endpoint for plugin options
- Added `objectcache_omit_analytics_footnote` filter
- Added connection retries
- Added support for `OBJECTCACHE_CONFIG` environment variable
- Added integration for WP User Manager
- Added `withTimeout()` and `withoutTimeout()` connection helpers

### Changed
- Pass PHPStan L7
- ⚠️ Made `Plugin`, `Configuration` and `Transaction` classes final
- ⚠️ Added `MeasuredObjectCacheInterface`
- ⚠️ Added `commands()`, `memoize()`, `ioWait()` and `withoutMutations()` to `ConnectionInterface`
- Reduced default (read-)timeout from `5.0s` to `2.5s`
- Reduced default retry interval from `100ms` to `25ms`
- Use `retry_interval` configuration option as backoff base
- Use `read_timeout` configuration option as backoff cap
- Use option API endpoint to save various settings
- Append backtrace to invalid cache key type log message
- Overhauled `flush_runtime()` and `flushRuntime()` methods
- Default to `default` group in `ObjectCache` helpers
- Accept any `callable` for `logger` configuration option
- Catch callback exceptions in `PhpRedisConnection::withoutMutations()`
- Improved retry/backoff support for Relay connections
- Support Relay event listeners for WordPress 6.0 `*_multiple()` methods
- Renamed `default` backoff to `smart`
- Only calculate cache size when needed and do so more memory-friendly

### Fixed
- Fixed handling invalid cache keys
- Fixed loading of settings styles when must-use is symlinked elsewhere
- Fixed dashboard widget positioning
- Relaxed URL validation
- Avoid timeouts when deleting by pattern
- Avoid rare key identifier collisions
- Prevent rare analytics restore failure
- Fixed up `PhpRedisReplicatedConnection` logic for `multi()` and `flushdb()` calls
- Import missing exception in `Configuration\Concerns\Sentinel` trait
- Fixed return value of `Updates::setUpPluginInfo()` when error occurred
- Drop `mixed` type in `PhpRedisClusterConnection::scanNode()`
- Prevent loading plugin more than once
- Don't hijack `action` parameter on dashboards

### Removed
- ⚠️ Removed deprecated `flushRuntimeCache()` helper

## v1.15.2 - 2022-06-30
### Added
- Added SQL queries metric

### Fixed
- Don't require `ext-redis` when running Relay transactions
- Only hide upgrade notice in must-use setups
- Tweak widget latency warning color
- Fixed rare error of `get_num_queries()` not being available

## v1.15.1 - 2022-06-19
### Added
- Show basename in diagnostics

### Changed
- Add Redis and Relay samples to analytics footnote
- Narrow type hint to `Transaction` in `PhpRedisConnection::executeMulti()`

### Fixed
- Handle PHP 7.0/7.1 environments gracefully
- Show error when using bad plugin slug
- Don't render analytics when not connected
- Avoid rare undefined index when not connected in Query Monitor
- Catch all `Throwable` errors in `ObjectCache::error()`, not only exceptions

## v1.15.0 - 2022-06-10

This releases introduces a settings page to keep an eye on cache analytics, manage plugin updates and use diagnostic tools.

### Added
- Added analytics charts under _Settings > Object Cache_
- Added plugin updates under _Settings > Object Cache -> Updates_
- Added various tools under _Settings > Object Cache -> Tools_
- Added WordPress 6.0's `wp_cache_*_multiple()` and `wp_cache_flush_runtime()` functions
- Added support for Redis Sentinel
- Hijack all transactions to allow command logging
- Added `X-Redirect-By` for all redirects
- Added `analytics`, `sentinels`, `service` and `relay.invalidations` configuration options
- Added REST API endpoint for analytics, cache groups and latency
- Added `master()` and `replicas()` to `PhpRedisReplicatedConnection`
- Added `nodes()` to `PhpRedisClusterConnection`
- Added `updates` configuration option
- Added `wp redis analytics` CLI command that mimics REST API endpoint

### Changed
- ⚠️ Require PHP 7.2+
- ⚠️ Require Relay v0.4.0
- ⚠️ Added `flush_runtime()` to `ObjectCacheInterface`
- ⚠️ Added `add_multiple()`, `set_multiple()` and `delete_multiple()` to `ObjectCacheInterface`
- ⚠️ Added `connectToSentinels()` and `connectToReplicatedServers()` `ConnectionInterface`
- Use group name as hash slot on cluster connections
- Deprecated `flushMemory()` in favor of `flushRuntime()` for naming consistency
- Redirect to settings after activation
- Allow analytics to be restored after cache flushes
- Only accept integers and non-empty strings as key names
- Dropped `string` type for `$key` in several `ObjectCacheInterface` methods
- Hide misleading Relay statistics in `wp redis info`
- Reverted: Store `alloptions` as individual keys when using Relay
- Increased Batcache compatibility
- Disabled `flush_network` option when using Redis cluster
- Marked PhpRedis v5.3.4 and older as outdated
- Catch `Throwable` everywhere, not `Exception`
- Use a single `window.objectcache` object
- Highlight expensive commands in Query Monitor
- Be more helpful about missing command logs in Query Monitor

### Fixed
- Fixed rare error when enabling drop-in
- Block `wp redis enable` when `WP_REDIS_CONFIG` is not set
- Fixed instantiating configuration without valid client extension present
- Avoid fatal error in `CommandsCollector` when no connection is established
- Show `rawCommand()` calls as actual commands in Query Monitor
- Various other bug fixes small additions and improvements
- Fixed selecting non-zero databases in `wp redis cli`
- Fixed rare rendering issue with `wp redis watch digest`
- Fixed normalization of IDNs
- Don't request filesystem write access to check for drop-in existence
- Fixed rare bug when flushing specific site via `wp redis flush 1337`

### Security
- Prevent risky plugin auto-updates
- Prevent plugin upgrades when using version control

## v1.14.5 - 2022-03-22
### Added
- Store `alloptions` as individual keys when using Relay
- Added health check for Relay configuration
- Added `Plugin::config()` helper method

### Changed
- Bump Relay requirement to v0.3.0
- Sped up `ObjectCache::id()` lookups by caching prefix
- Sped up `alloptions` hash deletion when using `async_flush`
- ⚠️ Renamed `SplitsAllOptions` trait to `SplitsAllOptionsIntoHash`

### Fixed
- Fixed support of older Query Monitor versions
- Added missing `retries` and `backoff` to diagnostics
- Avoid rare error in `Connection::ioWait()`
- Avoid rare `TypeError` in `Diagnostics`
- Avoid rare error in Query Monitor when no connection is present

## v1.14.4 - 2022-02-03
### Added
- Introduced `ObjectCache::Client` and `ObjectCache::clientName()`

### Changed
- Use `QM_VERSION` to detect Query Monitor version
- Convert logged commands names to uppercase
- Avoid log spam when calling Relay's `socketId()`, `socketKey()` or `license()`
- Make `isMustUse()` and `usingVCS()` helpers static
- Ignore all connection methods in Query Monitor backtraces
- Use new `qm/component_type/unknown` filter to set component type

### Fixed
- Avoid warnings when displaying rare commands in Query Monitor

## v1.14.3 - 2021-12-30
### Added
- Added file header health checks
- Use Query Monitor backtraces and components when available
- Added integrations for several user role editors
- Detect must-use installations and VCS checkouts
- Respect `WP_REDIS_UPDATES_DISABLED` constant and environment variable
- Added `Plugin::Capability` constant
- Added `Plugin::$directory` variable

### Changed
- Use `REQUEST_TIME_FLOAT` when available for improved accuracy
- Improve handling when `WP_REDIS_CONFIG` is set too late
- Use `current_screen` action to register dashboard widgets
- Use `wp_add_inline_style()` for widget styles

### Fixed
- Replaced non-functional capability checks using `map_meta_cap` with `user_has_cap` filter
- Restored plugin meta links
- Restored mu-plugin and drop-in update notices
- Disable update button in must-use setups
- Corrected value used for total Relay memory
- Avoid warning in `Diagnostics` when `WP_REDIS_CONFIG` is not set

## v1.14.2 - 2021-12-12
### Added
- Added `objectcache_dashboard_widget` filter
- Added `objectcache_network_dashboard_widget` filter
- Added `backoff` and `retries` configuration options
- Added `pre_objectcache_flush` filter to short-circuit the flushing
- Safeguard against update confusion attacks using `Update URI` plugin header
- Added `shared` configuration option to show key count instead of database size in shared environment

### Changed
- Use decorrelated jitter backoff algorithm by default for all connections
- Append analytics comment to all XML feeds
- Show license state in Query Monitor and diagnostics
- Collect Relay key counts in metrics
- Bump Relay requirement to v0.2.2
- Memorize `Relay::stats()` calls

### Fixed
- Several PHP 8.1 compatibility fixes

## v1.14.1 - 2021-10-26
### Fixed
- Fixed invalid nonce errors

### Removed
- Removed unnecessary `phpversion()` and `setOption()` calls in `RelayConnector`

## v1.14.0 - 2021-10-26

⚠️ This release contains minor breaking changes.

### Added
- ⚠️ Added `Connector::supports()` method
- ⚠️ Added `ObjectCacheInterface::boot()` method
- Added Twemproxy support
- Added `ObjectCache::metrics()` method
- Added `Connection::ioWait()` method
- Added `Plugin::flush()` helper
- Added several `Diagnostic` helper methods
- Display Relay cache size in dashboard widget
- Track reads, writes, hits and misses counts
- Added cluster support to `wp redis cli`
- Allow clusters to be flushed asynchronously
- Added backbone for analytics and `wp redis watch` command with 3 watcher types
- Added environment type and host to all diagnostics

### Changed
- ⚠️ Changed dashboard widget identifier to `dashboard_objectcache`
- Deprecated `$GLOBALS['RedisCachePro']`, use to `$GLOBALS['ObjectCachePro']` instead
- Deprecated `rediscache_manage` capability, use to `objectcache_manage` instead
- Deprecated `rediscache_validate_dropin` filter, use `objectcache_validate_dropin` instead
- Deprecated `rediscache_validate_dropin_version` filter, use `objectcache_validate_dropin_version` instead
- Deprecated `rediscache_check_filesystem` filter, use `objectcache_check_filesystem` instead
- Trigger `E_USER_WARNING` instead of `E_USER_NOTICE` in `ObjectCache::__get()`
- Always use asynchronous flushing with Relay
- Flush the cache when calling `wp redis enable` and `wp redis disable`, unless `--skip-flush` is passed
- Don't `EXPIRE` the `alloptions` hash map
- Memorize `PING` and `INFO` results when feasible
- Register shutdown function to close cache in `wp_cache_init()`
- Store prefetches when closing cache
- Only log incompatible key prefetch warnings in `debug` mode
- Show extended metrics in Query Monitor panel
- Simplified Debug Bar integration
- Moved CLI commands into `Console` namespace
- Boot connector when setting client in the configuration, not in `wp_cache_init()`
- Improved error messages when checking of compression/serializer in configuration
- Use SPL autoload function in `bootstrap.php`
- Increased priority of dashboard widget
- Set default `timeout` and `read_timeout` to `5` seconds
- Set default `retry_interval` to `100` milliseconds
- Always verify invalid licenses when running health checks
- Bumped the HTTP timeout for license health checks to `15` seconds
- Invalidate drop-in file opcode when disabling the cache
- Cache `get_plugin_data()` calls to improve performance
- Flush the cache when enabling/disabling the drop-in via dashboard widget and when deactivating the plugin
- Support must-use Query Monitor setups
- Bumped `prefix` limit to `32` characters

### Fixed
- Speed up API health check
- Fixed some rare PHP notices
- Fixed fatal error when using `url` option without password
- Prevent unnecessary config calls in `RelayObjectCache`
- Support `object-cache-pro.php` basename setups
- Fix PHP 7.1 and older compatibility in `License`

### Removed
- Removed unnecessary confirm dialogs from dashboard widget action

## v1.13.3 - 2021-06-08
### Added
- Support passing function names to `logger` configuration option

### Changed
- Improved [Relay](https://relaycache.com) integration and support
- Improved unsupported `compression` and `serializer` error messages
- Renamed more things to "Object Cache Pro"

### Fixed
- Support `url` option without usernames
- Ensure `wp_debug_backtrace_summary()` is loaded to increase Batcache compatibility

### Security
- Hide passwords in `url` from diagnostics

## v1.13.2 - 2021-04-29
### Added
- Added `relay_listeners` configuration option
- Added a `BacktraceLogger` logger for easy debugging
- Added wildcard support in non-prefetchable group names

### Changed
- Split up `setMutationOptions()` methods

### Fixed
- Fixed flushing clusters when using a TLS connection

## v1.13.1 - 2021-04-14
### Changed
- Disable prefetching for CLI and API requests
- Added `userlogins` and `wc_session_id` to non-prefetchable groups
- Prevent even more `__PHP_Incomplete_Class` errors when using prefetching

### Fixed
- Fixed type error in `PrefetchesKeys`

## v1.13.0 - 2021-04-12
### Added
- Added support for [Relay](https://relaycache.com)
- Added `client` configuration option
- Added `tls_options` configuration option
- Added `ObjectCache::withoutMutations()` helper
- Added `PrefetchesKeys::deletePrefetches()` helper
- Added `info` log lines to `wp_cache_init()`
- Added wildcard support in non-persistent group names
- Show command count and argument class names in Query Monitor

### Changed
- Respect `debug` configuration option in `wp_cache_init()`
- Added `Connector::boot(): void` interface method
- Deprecated `WP_REDIS_PHPREDIS_OPTIONS` constant
- Renamed several internal exceptions
- Access fully qualified constants
- Expect `PhpRedisConnection` in `PhpRedisObjectCache` constructor
- Use high resolution time when available
- Increased command time decimals from `2` to `4`
- Refactored license code to be more graceful
- Prevent `__PHP_Incomplete_Class` errors when using prefetching

### Fixed
- Fixed flushing networks when using the `site` or `global` option
- Fixed preloading in multisite environments
- Fixed prefetches count discrepancies

## v1.12.0 - 2020-12-23
### Added
- Improved PHP 8 compatibility
- Added support for Batcache
- Support flagging groups as non-prefetchable
- Added `ObjectCache::deleteFromMemory(string $key, string $group)` method
- Added `ObjectCache::flushMemory()` in favor of `ObjectCache::flushRuntimeCache()`
- Added `rediscache_validate_dropin` filter
- Added `rediscache_validate_dropin_version` filter

### Changed
- Support loading the object cache as early as `advanced-cache.php`
- Changed default value of `cluster_failover` to `error` to improve stability
- Refactored `flushBlog(int $siteId, string $flush_network = null)` method
- Check `wp_is_file_mod_allowed('object_cache_dropin')` before automatically updating drop-in
- Marked PHP 7.3 as outdated

### Fixed
- Prevent another rare undefined variable notice in `wp_cache_init()`
- Resolve incompatibility with Query Monitor 3.6.5
- Updated some links to the documentation

## v1.11.1 - 2020-11-12
### Changed
- Only preload groups with at least two keys

### Fixed
- Convert integer group names to strings

## v1.11.0 - 2020-11-11
### Added
- Added prefetching 🚀
- Added `ObjectCache::flushRuntimeCache()` method
- Added `PhpRedisConnection::withoutMutations()` method
- Added support for Query Monitor's new backtraces

### Changed
- Improved memory usage display in widget
- Speed up command execution when using replication
- Improved parameter formatting in Query Monitor
- Attach backtrace as array along with backtrace summary to log messages
- Moved runtime cache helpers from to `ObjectCache` class

### Fixed
- Fixed updating split `alloptions` hash options with equivalent values
- Send `alloptions` hash read commands to the master when using replication
- Prevent rare undefined variable notice in `wp_cache_init()`

## v1.10.2 - 2020-09-28
### Added
- Overload generic `cache_hits` and `cache_misses` properties

### Changed
- Always return a `Configuration` from `Configuration::safelyFrom()`
- Use invalid configuration option name (not method name) in error log message

### Fixed
- Use key caching in multisite environments
- Added suffix to MU and Drop-in stubs to avoid invalid plugin header error when activating

## v1.10.1 - 2020-09-16
### Fixed
- Fixed an issue with non-persistent, numeric keys in `get_multiple()`

## v1.10.0 - 2020-09-15
### Added
- Show connection class in Query Monitor
- Added `Configuration::parseUrl()` helper
- Added support for replicated connections
- Show configuration instantiation errors in widget and site health

### Changed
- Moved `RedisCachePro\Config` class to `RedisCachePro\Configuration\Configuration`
- Renamed `slave_failover` configuration option to `cluster_failover` ✊🏿
- Extracted cluster related configuration into `RedisCachePro\Configuration\Concerns\Cluster`
- Trim log messages to 400 characters in Debug Bar log
- Throw `RedisConfigValueException` for invalid configuration values instead of `RedisConfigException`

### Fixed
- Create a proper configuration object when cache instantiation fails
- Escape command parameters in Query Monitor extension
- Moved Redis related information from `ObjectCache::info()` to `PhpRedisObjectCache::info()`
- Always strip `unix://` from PhpRedis host parameter
- Ensure `PhpRedisObjectCache::info()->status` returns a boolean
- Prevent invalid configuration values from booting plugin

## v1.9.2 - 2020-08-29
### Added
- Look for `object-cache-pro` directory in must-use stub

### Fixed
- Fixed host scheme in PhpRedis 5.0.2 and older

## v1.9.1 - 2020-08-26
### Added
- Added `wp_cache_remember()` and `wp_cache_sear()` functions

### Changed
- Pointed all URLs to the new domain `objectcache.pro`
- Change log prefix from `rediscache` to `objectcache`
- Slightly sped up API loading in object cache drop-in

### Fixed
- Only throw exception in mu-plugin and drop-in when `WP_DEBUG` is true

## v1.9.0 - 2020-08-15
### Added
- Added `url` configuration option
- Added `scheme` configuration option
- Added support for `options` parameter added in PhpRedis 5.3
- Added support for LZ4 compression (PhpRedis 5.3+)

### Changed
- Support connecting to sockets and using TLS in `wp redis cli`
- Convert `host` configuration option to lowercase
- Set `scheme` when included in `host` configuration option

### Fixed
- Fixed getting multiple numeric keys in `ArrayObjectCache`
- Fixed setting `port`, `database`, `maxttl` and `retry_interval` as numeric string

## v1.8.2 - 2020-08-11
### Changed
- Reverted eager plugin name change in v1.8.1
- Updated plugin URL to new domain to avoid redirects

### Fixed
- Don't flag drop-in as invalid when it's just outdated

## v1.8.1 - 2020-08-11
### Changed
- Look for API in `object-cache-pro` directories

### Fixed
- Fixed an issue with numeric keys and improved error handling in `get_multiple()`

## v1.8.0 - 2020-07-23
### Added
- Added `username` configuration option for Redis 6 ACL support
- Added `cache` configuration option to set a custom object cache class
- Added `connector` configuration option to set a custom connector class
- Added `flush_network` configuration option (defaults to `all`)
- Support flushing individual sites via _Network Admin > Sites_
- Added `--skip-flush-notice` option to `wp redis enable` command
- Added health check to ensure asynchronous flushing is supported
- Added health check to ensure drop-in can be managed
- Added intuitive alias for all WP CLI commands
- Added support for `wp_cache_get_multiple()` introduced in WP 5.5

### Changed
- Renamed "Dropin" to "Drop-in" everywhere
- Support flushing individual sites using `wp redis flush`
- Hide Redis memory from dashboard widget in multisite environments
- Display notice when license token is not set or invalid, as well as when the license is unpaid or canceled

### Fixed
- Explicitly set permissions of `object-cache.php` to `FS_CHMOD_FILE`

## v1.7.3 - 2020-07-10
### Fixed

- Support older versions of Query Monitor
- Ignore HTTP errors for license verification requests
- Prevent undefined index notice in `ObjectCache::info()`
- Prevent call to undefined function in `Licensing::telemetry()`

## v1.7.2 - 2020-07-09
### Added
- Use `wp_opcache_invalidate()` on drop-in
- Refactored `Diagnostics` to use `Diagnostic` objects

### Changed
- Minor dashboard widget improvements
- Minor Query Monitor extension improvements
- Cleanup plugin options upon deactivation
- Disable FOSS version when activating plugin to avoid confusion

### Fixed
- Escape more HTML outputs
- Prevent unnecessary license verification requests

## v1.7.1 - 2020-06-08
### Fixed
- Always send `setOption()` values as string
- Fixed Query Monitor panels not showing up for some setups
- Fixed `ArrayObjectCache` fallback instantiation in `wp_cache_init()`
- Format all commands parameters using `json_encode()` in Query Monitor panel

## v1.7.0 - 2020-05-30
### Added
- Added support for Query Monitor
- Added context to license issues in the dashboard widget
- Show updates for must-use plugin and object cache drop-in

### Changed
- Improved formatting of config values in diagnostics
- Don't highlight `noeviction` policy when maxTTL is set

### Fixed
- Prevent unnecessary plugin update requests

## v1.6.0 - 2020-05-11
### Added
- Support PHP 7.0 and PhpRedis 3.1.1
- Indicate missing license token in dashboard widget

### Changed
- Switched to `WP_CLI\Utils\proc_open_compat()` for `wp redis cli`
- Ping Redis during object cache initialization to catch `LOADING` errors

### Fixed
- Fixed potential `TypeError` during `upgrader_process_complete` action

## v1.5.1 - 2020-04-29
### Fixed
- Fixed global group cache keys

### Security
- Prevent XSS injection using cache group names when using Debug Bar

## v1.5.0 - 2020-04-22
### Added
- Added `Requires PHP` and `Network` to plugin header fields
- Show supported compression formats in site health

### Changed
- Initialize plugin after all plugins have been loaded
- Improved the plugin version and basename detection
- Improved muting the `wp redis cli` auth warning
- Don't require setting `port` when connecting to a unix socket
- Validate config connection information before connecting
- Always inline widget styles (1015 bytes)
- Always inject plugin details into `update_plugins` transient
- Improved obfuscation of sensitive values
- Hide health link from plugin actions in WP <5.2 and multisite networks
- Prevent widget color clashing with color scheme

### Fixed
- Fixed detection of multisite networks
- Fixed setting global and non-persistent groups
- Fixed notices in Debug Bar extension
- Fixed `INFO` command when using cluster

### Removed
- Removed `wp_clear_scheduled_hook` for `rediscache_report`

## v1.4.0 - 2020-02-27
### Added
- Added support for storing `alloptions` key as hash
- Added `wp redis cli` command to spawn `redis-cli` process with configuration
- Support `WP_REDIS_DIR` constant in `mu-plugin.php` stub

### Changed
- Ensure object cache drop-in is valid before flushing via CLI
- Colorized `wp redis flush` errors

### Fixed
- Fixed typo in `RedisConfigMissingException`
- Fixed logs missing from Debug Bar
- Fixed cloning logic in `PhpRedisObjectCache::storeInMemory()`
- Inline styles when plugin is symlinked or located outside of web root

## v1.3.0 - 2020-02-06
### Added
- Added support for asynchronous flushing
- Added support for data compression using LZF and Zstandard
- Added network admin dashboard widget
- Added `wp redis flush` command with support for `--async` flag
- Automatically update drop-in after plugin update
- Show used and max memory in widget, site health and Debug Bar

### Changed
- Switched to using `ErrorLogLogger` by default
- The `ArrayLogger` now extends `ErrorLogLogger` and vice versa
- The log levels now default to `['emergency', 'alert', 'critical', 'error']`
- Changed log level of `ObjectCache::error()` from `critical` to `error`
- Introduced `PhpRedisMissingException` and `PhpRedisOutdatedException`
- Attempt direct filesystem access when WP filesystem initialization fails
- Renamed internal cache methods in `PhpRedisObjectCache` to be more descriptive
- Capture more errors by using `Throwable` in some places
- Moved Debug Bar HTML into template files
- Support setting `log_levels` configuration option to `null`

### Removed
- Support setting `token` and `password` to `null`
- Removed captured errors from site health information

## v1.2.1 - 2020-01-20
### Added
- Added health checks link to plugin actions

### Changed
- Made initialization exceptions more helpful
- Escape HTML in Debug Bar log messages
- Improved pinging cluster nodes

### Fixed
- Fixed duplicate prefix when using cluster
- Fixed undefined index notices in `Licensing`
- Fixed a issue when loading widget styles as must-use plugin
- Resolved minor spelling mistakes

## v1.2.0 - 2019-11-29
### Added
- Added dashboard widget
- Added support for automatic WordPress updates
- Added diagnostic tests and information to _Tools > Site Health_
- Added `token` configuration option to set license token

### Changed
- Disable object cache when deactivating/uninstalling the plugin

### Fixed
- Fixed DebugBar integration on case-sensitive filesystems

## v1.1.0 - 2019-11-19
### Added
- Added log levels
- Added `WP_REDIS_DISABLED` environment variable

### Changed
- Use `PhpRedisConnection` for each master when flushing clusters
- Obfuscate all non-`null` passwords in diagnostics
- Allow password to be `null` for more convenient fallbacks

### Fixed
- Prevent timeouts when flushing database
- Use inline styles to clear floats in Debug Bar panels

### Security
- Obfuscate password in constants section of diagnostics

## v1.0.0 - 2019-11-01
### Added
- Initial stable release
