# Changelog

## v4.0.10 - 2026-02-26

### What's Changed

* Cast LARAVEL_START const to float by @daniser in https://github.com/fruitcake/laravel-debugbar/pull/1968
* Laravel 13.x Compatibility by @laravel-shift in https://github.com/fruitcake/laravel-debugbar/pull/1969
* GateCollector backtrace need more steps by @angeljqv in https://github.com/fruitcake/laravel-debugbar/pull/1972
* Support custom messages on QueryCollector by @angeljqv in https://github.com/fruitcake/laravel-debugbar/pull/1970
* Use upstream collect by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1960

### New Contributors

* @daniser made their first contribution in https://github.com/fruitcake/laravel-debugbar/pull/1968
* @laravel-shift made their first contribution in https://github.com/fruitcake/laravel-debugbar/pull/1969

**Full Changelog**: https://github.com/fruitcake/laravel-debugbar/compare/v4.0.9...v4.0.10

## v4.0.9 - 2026-02-17

### What's Changed

* Check mail is started by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1967

**Full Changelog**: https://github.com/fruitcake/laravel-debugbar/compare/v4.0.8...v4.0.9

## v4.0.8 - 2026-02-14

### What's Changed

* Use message id instead of subject for mail collector by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1965

**Full Changelog**: https://github.com/fruitcake/laravel-debugbar/compare/v4.0.7...v4.0.8

## v4.0.7 - 2026-02-06

### What's Changed

* chore: replace deprecated Request::get() with Request::input() by @calebdw in https://github.com/fruitcake/laravel-debugbar/pull/1957
* Fix utf-8 encoding by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1959

**Full Changelog**: https://github.com/fruitcake/laravel-debugbar/compare/v4.0.6...v4.0.7

## v4.0.6 - 2026-02-04

### What's Changed

* Use Dispatcher contract by @bytestream in https://github.com/fruitcake/laravel-debugbar/pull/1954
* fix: urlencode cache key by @calebdw in https://github.com/fruitcake/laravel-debugbar/pull/1955
* Handle missing bindings in SQL formatting by @erikn69 in https://github.com/fruitcake/laravel-debugbar/pull/1956

### New Contributors

* @bytestream made their first contribution in https://github.com/fruitcake/laravel-debugbar/pull/1954
* @calebdw made their first contribution in https://github.com/fruitcake/laravel-debugbar/pull/1955

**Full Changelog**: https://github.com/fruitcake/laravel-debugbar/compare/v4.0.5...v4.0.6

## v4.0.5 - 2026-01-29

### What's Changed

* Show params table for explain button by @erikn69 in https://github.com/fruitcake/laravel-debugbar/pull/1949
* Change bindings return value on limited by @erikn69 in https://github.com/fruitcake/laravel-debugbar/pull/1948
* use GateEvaluated event by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1951
* Fix livewire deprecations errors by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1952

**Full Changelog**: https://github.com/fruitcake/laravel-debugbar/compare/v4.0.4...v4.0.5

## v4.0.4 - 2026-01-29

### What's Changed

* Bump workflows actions by @erikn69 in https://github.com/fruitcake/laravel-debugbar/pull/1946
* Allow ability parameter to accept integer type by @jeffersongoncalves in https://github.com/fruitcake/laravel-debugbar/pull/1947

### New Contributors

* @jeffersongoncalves made their first contribution in https://github.com/fruitcake/laravel-debugbar/pull/1947

**Full Changelog**: https://github.com/fruitcake/laravel-debugbar/compare/v4.0.3...v4.0.4

## v4.0.3 - 2026-01-26

### What's Changed

* Remove find cache in favor of upstream optimization by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1939
* Update onCacheEvent to accept multiple event types by @Yahav in https://github.com/fruitcake/laravel-debugbar/pull/1943
* Fix cache collector route by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1944

### New Contributors

* @Yahav made their first contribution in https://github.com/fruitcake/laravel-debugbar/pull/1943

**Full Changelog**: https://github.com/fruitcake/laravel-debugbar/compare/v4.0.2...v4.0.3

## v4.0.2 - 2026-01-24

### What's Changed

* Fix Auth Collector by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1937
* Fix session collector by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1938

**Full Changelog**: https://github.com/fruitcake/laravel-debugbar/compare/v4.0.1...v4.0.2

## v4.0.1 - 2026-01-24

### What's Changed

* Fix explain table css on queries widget by @erikn69 in https://github.com/fruitcake/laravel-debugbar/pull/1929
* Check if Telescope is recording by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1931
* Update namespaces in readme by @sajjadhossainshohag in https://github.com/fruitcake/laravel-debugbar/pull/1932
* Add backtrace path by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1933
* Update vendor name, fix release notes by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1934
* Add link class by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1935
* Collected jobs from queue by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1936

### New Contributors

* @sajjadhossainshohag made their first contribution in https://github.com/fruitcake/laravel-debugbar/pull/1932

**Full Changelog**: https://github.com/fruitcake/laravel-debugbar/compare/v4.0.0...v4.0.1

## v4.0.0 - 2026-01-23

### Laravel Debugbar 4.0

### Release notes

See https://fruitcake.nl/blog/laravel-debugbar-v4-release for the biggest changes.

This brings the updates from php-debugbar 3.x to Laravel Debugbar. See https://github.com/php-debugbar/php-debugbar/releases/tag/v3.0.0 for the upstream changes to php-debugbar.

### Updating

The name has changed, so remove the old package first:

`composer remove barryvdh/laravel-debugbar --dev --no-scripts`

Then install the new package

`composer require fruitcake/laravel-debugbar --with-dependencies`

Check the https://github.com/fruitcake/laravel-debugbar/blob/master/UPGRADE.md for any changes.

### All Changes

* Prepare for Debugbar 3.x by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1828
* Fix 4.x queries by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1832
* Remove deprecations, tweak default config by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1833
* Always render widget in footer by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1834
* Fix null handling quoting in emulateQuote[QueryCollector] by @erikn69 in https://github.com/fruitcake/laravel-debugbar/pull/1835
* Update workflows / tools, add static analyses, fix some errors by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1836
* Revert event config by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1837
* Remove socket storage by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1839
* Remove Lumen support by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1838
* Remove icon by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1840
* Fix phpstan by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1841
* Remove PDO extension by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1842
* Extend base sql widget by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1843
* Fix shell quotes in README by @szepeviktor in https://github.com/fruitcake/laravel-debugbar/pull/1264
* refactor: improve routes formats by @jbidad in https://github.com/fruitcake/laravel-debugbar/pull/1392
* remove copy and hints by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1844
* Check response for avoid inject debugbar on json ajax by @erikn69 in https://github.com/fruitcake/laravel-debugbar/pull/1558
* Show estimate of cache byte usage by @erikn69 in https://github.com/fruitcake/laravel-debugbar/pull/1764
* Check string by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1845
* Use original background by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1847
* Add DataProviders for easier maintenance by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1846
* Feat custom collectors by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1848
* Tweak config by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1849
* Feat phpdebugbar symfony by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1850
* Improve Livewire collection and view detection for components by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1853
* Builds docs from source by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1854
* Fix default for excluded events by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1856
* Remove icon overrides by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1857
* Restore Mail collector timeline by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1858
* Add HTTP client collector by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1859
* Add http client to docs by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1860
* Update JavascriptRenderer for upstream changes by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1861
* Simplify Asset Renderer by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1862
* Bring back logs collector by @erikn69 in https://github.com/fruitcake/laravel-debugbar/pull/1863
* Use message context for gate and logs by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1866
* Updates tests for new beta by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1867
* Reduce styling overrides by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1864
* Use symfony-bridge by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1868
* Set livewire sentence by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1869
* Fix timeline by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1871
* Fix storage by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1872
* Seperate listeners from data in events by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1873
* Add casters for heavy objects by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1874
* Fix tests by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1876
* TWeak livewire properties by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1877
* Move namespace to Fruitcake\LaravelDebugbar by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1875
* Replace old package name by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1878
* Fix explain option access in DatabaseCollectorProvider by @erikn69 in https://github.com/fruitcake/laravel-debugbar/pull/1879
* Update .gitattributes by @erikn69 in https://github.com/fruitcake/laravel-debugbar/pull/1881
* Stricter types by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1884
* Add docs directory to export-ignore in .gitattributes by @erikn69 in https://github.com/fruitcake/laravel-debugbar/pull/1883
* Cleanup by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1885
* Fix docs tests by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1886
* Fix cache widget by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1887
* Fix checkVersion accessibility by @angeljqv in https://github.com/fruitcake/laravel-debugbar/pull/1889
* Check signature by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1888
* Add Inertia collector by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1890
* Improve storage scan by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1891
* Use upstream file storage and request generator by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1892
* Optimize livewire by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1893
* Test Livewire 2/3/4 by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1894
* Reset interfaces on Octane request, use current config by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1895
* Separate the debugbar from the application load(TimeCollector) by @erikn69 in https://github.com/fruitcake/laravel-debugbar/pull/1896
* Optimize serviceprovider by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1897
* Octane singleton by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1898
* Tweak constructors and config by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1899
* Tweak pennant by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1900
* Time octane reset by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1901
* Tweak booting time by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1902
* Tweak twig by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1903
* Always ensure time/exceptions/messages are available, to log before b… by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1904
* Tweak config values by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1906
* Tweak subscribers by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1905
* Remove request instances by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1907
* Update console collecting by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1908
* Fix cache events by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1909
* Tweak handle by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1910
* Add octane request start by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1911
* Small reset tweaks by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1912
* Add some timeline options by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1913
* Ensure latest request is used by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1915
* Check if octane needs to enable/disbale by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1917
* Use cookies instead of session, events instead of middleware by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1914
* Update tests for Livewire 3 and 4 by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1918
* collect on terminate by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1919
* Bump lodash from 4.17.21 to 4.17.23 by @dependabot[bot] in https://github.com/fruitcake/laravel-debugbar/pull/1920
* Restore ulid requestids by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1921
* Use openhandler http driver, set etag by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1922
* Check if params table is set by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1923
* Fix event data by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1924
* Update RequestCollector for CLI usage by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1925
* Tweak ClearCommand for uninstall by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1927
* Catch resolve errors by @barryvdh in https://github.com/fruitcake/laravel-debugbar/pull/1928

### New Contributors

* @szepeviktor made their first contribution in https://github.com/fruitcake/laravel-debugbar/pull/1264
* @jbidad made their first contribution in https://github.com/fruitcake/laravel-debugbar/pull/1392
* @dependabot[bot] made their first contribution in https://github.com/fruitcake/laravel-debugbar/pull/1920

**Full Changelog**: https://github.com/fruitcake/laravel-debugbar/compare/v3.16.3...v4.0.0

## v3.16.4 - 2026-01-23

- Add new fruitcake namespace to exclude from query backtrace.

**Full Changelog**: https://github.com/fruitcake/laravel-debugbar/compare/v3.16.3...v3.16.4

## v4.0-beta.11 - 2026-01-06

### What's Changed

* Simplify Asset Renderer by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1862

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v4.0-beta.10...v4.0-beta.11

## v4.0-beta.9 - 2026-01-05

### What's Changed

* Remove icon overrides by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1857
* Restore Mail collector timeline by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1858
* Add HTTP client collector by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1859
* Add http client to docs by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1860

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v4.0-beta.8...v4.0-beta.9

## v4.0-beta.8 - 2026-01-05

### What's Changed

* Builds docs from source by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1854
* Fix default for excluded events by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1856

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v4.0-beta.7...v4.0-beta.8

## v4.0-beta.7 - 2026-01-05

### What's Changed

* Improve Livewire collection and view detection for components by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1853

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v4.0-beta.6...v4.0-beta.7

## v3.16.3 - 2025-12-26

### What's Changed

* Update symfony/finder version constraint to include 8 by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1830
* Allow Symfony v8 by @jnoordsij in https://github.com/barryvdh/laravel-debugbar/pull/1827
* Add error_level config option to filter error handler reporting (#1373) by @elliota43 in https://github.com/barryvdh/laravel-debugbar/pull/1825
* Add support for Cursor, Windsurf, and additional editor configurations by @nguyentranchung in https://github.com/barryvdh/laravel-debugbar/pull/1823
* Don't create <a> tags with the onclick attribute by @PeterMead in https://github.com/barryvdh/laravel-debugbar/pull/1820
* docs: Add conditional check for Debugbar alias registration by @erhanurgun in https://github.com/barryvdh/laravel-debugbar/pull/1829

### New Contributors

* @elliota43 made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1825
* @nguyentranchung made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1823
* @PeterMead made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1820
* @erhanurgun made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1829

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v3.16.2...v3.16.3

## v3.16.2 - 2025-12-16

### What's Changed

* Remove default null value env by @Erulezz in https://github.com/barryvdh/laravel-debugbar/pull/1815
* Remove --ignore-platform-req=php+ on integration test setup by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1814
* Remove calls to PHP 8.5-deprecated `setAccessible` by @jnoordsij in https://github.com/barryvdh/laravel-debugbar/pull/1822

### New Contributors

* @Erulezz made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1815

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v3.16.1...v3.16.2

## v3.16.1 - 2025-11-19

### What's Changed

* Slow threshold highlight on queries by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1805
* (fix) trim last line breaks on logs by @angeljqv in https://github.com/barryvdh/laravel-debugbar/pull/1806
* fix: Typo by @aurac in https://github.com/barryvdh/laravel-debugbar/pull/1810
* Test on PHP 8.5 by @jnoordsij in https://github.com/barryvdh/laravel-debugbar/pull/1811
* Add '_boost*' to debugbar exceptions by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1818
* Dropped Laravel 9 support

### New Contributors

* @aurac made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1810

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v3.16.0...v3.16.1

## v3.16.0 - 2025-07-21

### What's Changed

* Make all scalar config values configurable through environment variables by @wimski in https://github.com/barryvdh/laravel-debugbar/pull/1784
* Check if file exists on FilesystemStorage by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1790
* Bump php-debugbar by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1791
* Fix counter tests by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1792
* `$group` arg support on TimelineCollectors methods by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1789
* Collect other eloquent model events by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1781
* Add new cache events on CacheCollector by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1773
* Exclude events on EventCollector by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1786
* Use `addWarning` on warnings, silenced errors, notices by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1767
* Do not rely on DB::connection() to get information in query collector by @cweiske in https://github.com/barryvdh/laravel-debugbar/pull/1779
* Trace file for Gate checks(GateCollector) by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1770
* Fix support for PDOExceptions by @LukeTowers in https://github.com/barryvdh/laravel-debugbar/pull/1752
* Time measure on cache events by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1794
* fix debugbar for Lumen usage by @flibidi67 in https://github.com/barryvdh/laravel-debugbar/pull/1796
* Custom path for Inertia views by @joaopms in https://github.com/barryvdh/laravel-debugbar/pull/1797
* Better contrast in dark theme titles. by @angeljqv in https://github.com/barryvdh/laravel-debugbar/pull/1798

### New Contributors

* @wimski made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1784
* @cweiske made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1779
* @flibidi67 made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1796
* @joaopms made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1797

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v3.15.4...v3.16.0

## v3.15.4 - 2025-04-16

### What's Changed

* Remove html `<a/>` tag from route on clockwork by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1777
* Fix default for capturing dd/dump by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1783

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v3.15.3...v3.15.4

## v3.15.3 - 2025-04-08

### What's Changed

* Add condition for implemented query grammar by @rikwillems in https://github.com/barryvdh/laravel-debugbar/pull/1757
* Collect dumps on message collector by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1759
* Fix `capture_dumps` option on laravel `dd();` by @parallels999 in https://github.com/barryvdh/laravel-debugbar/pull/1762
* Preserve laravel error handler by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1760
* Fix `Trying to access array offset on false on LogsCollector.php` by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1763
* Update css theme for views widget by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1768
* Fix laravel-debugbar.css on query widget by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1765
* Use htmlvardumper if available on CacheCollector by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1766
* Update QueryCollector.php fix issue #1775 by @Mathias-DS in https://github.com/barryvdh/laravel-debugbar/pull/1776
* Better grouping the events count by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1774

### New Contributors

* @rikwillems made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1757
* @Mathias-DS made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1776

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v3.15.2...v3.15.3

## v3.15.2 - 2025-02-25

### What's Changed

* Fix empty tabs on clockwork by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1750
* fix: Ignore info query statements in Clockwork converter by @boserup in https://github.com/barryvdh/laravel-debugbar/pull/1749
* Check if request controller is string by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1751

### New Contributors

* @boserup made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1749

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v3.15.1...v3.15.2

## v3.15.1 - 2025-02-24

### What's Changed

* Hide more empty tabs  by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1742
* Always show application by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1745
* Add conflict with old debugbar by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1746

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v3.15.0...v3.15.1

## v3.15.0 - 2025-02-21

### What's Changed

* Add middleware to web to save session by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1710
* Check web middleware by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1712
* Add special `dev` to composer keywords by @jnoordsij in https://github.com/barryvdh/laravel-debugbar/pull/1713
* Removed extra sentence by @cheack in https://github.com/barryvdh/laravel-debugbar/pull/1714
* Hide empty tabs by default by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1711
* Combine route info with Request by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1720
* fix: The log is not processed correctly when it consists of multiple lines. by @uniho in https://github.com/barryvdh/laravel-debugbar/pull/1721
* [WIP] Use php-debugbar dark theme, move to variables by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1717
* Remove openhandler overrides by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1723
* Drop Lumen And Laravel 9 by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1725
* Use tooltip for Laravel collector by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1724
* Add more data to timeline by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1726
* Laravel version preview as repo branch name by @angeljqv in https://github.com/barryvdh/laravel-debugbar/pull/1727
* Laravel 12 support by @jonnott in https://github.com/barryvdh/laravel-debugbar/pull/1730
* Preview action_name on request tooltip by @angeljqv in https://github.com/barryvdh/laravel-debugbar/pull/1728
* Map tooltips by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1732
* Add back L9 by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1734
* Fix tooltip url by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1735
* Show request status as badge by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1736
* Fix request badge by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1737
* Use Laravel ULID for key by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1738
* defer datasets by config option by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1739
* Reorder request tab by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1740
* Defer config by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1741

### New Contributors

* @cheack made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1714
* @angeljqv made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1727
* @jonnott made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1730

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v3.14.10...v3.15.0

## v3.14.10 - 2024-12-23

### What's Changed

* Fix Debugbar spelling inconsistencies by @ralphjsmit in https://github.com/barryvdh/laravel-debugbar/pull/1626
* Fix Visual Explain confirm message by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1709

### New Contributors

* @ralphjsmit made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1626

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v3.14.9...v3.14.10

## v3.14.9 - 2024-11-25

### What's Changed

* Fix custom prototype array by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1706

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v3.14.8...v3.14.9

## v3.14.8 - 2024-11-25

### What's Changed

* Add fix + failing test for custom array prototype by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1705

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v3.14.7...v3.14.8

## v3.14.7 - 2024-11-14

### What's Changed

* Make better use of query tab space by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1694
* Do not open query details on text selecting by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1693
* Add (initial) support for PHP 8.4 by @jnoordsij in https://github.com/barryvdh/laravel-debugbar/pull/1631
* More warnings by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1696
* Fix sql-duplicate highlight by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1699
* ci: Use GitHub Actions V4 by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1700
* Fix "Uncaught TypeError: is not iterable" by @erikn69 in https://github.com/barryvdh/laravel-debugbar/pull/1701
* Fix Exception when QueryCollector softLimit exceeded by @johnkary in https://github.com/barryvdh/laravel-debugbar/pull/1702
* Test soft/hard limit queries by @barryvdh in https://github.com/barryvdh/laravel-debugbar/pull/1703

### New Contributors

* @johnkary made their first contribution in https://github.com/barryvdh/laravel-debugbar/pull/1702

**Full Changelog**: https://github.com/barryvdh/laravel-debugbar/compare/v3.14.6...v3.14.7
