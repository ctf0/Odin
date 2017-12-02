<?php

namespace ctf0\Odin;

require_once dirname(__FILE__) . '/../php-diff/SideBySide.php';

class Odin
{
    /**
     * render revision diff data to table.
     *
     * if we have a json column,
     * we first sort the keys to avoid issues in comparison.
     *
     * then if we only have a field but not the other,
     * we replicate "new" to "old" or vice/versa and reset its keys
     *
     * last we combine old & new of each key to make sure we
     * always get the correct comparison table
     *
     * @param [type] $rev [description]
     *
     * @return [type] [description]
     */
    public function toHtml($rev)
    {
        $str   = '';
        $multi = [];

        foreach ($rev->getModified() as $col => $data) {
            $data['new'] = json_decode(array_get($rev->new_values, $col), true) ?: array_get($rev->new_values, $col);
            $data['old'] = json_decode(array_get($rev->old_values, $col), true) ?: array_get($rev->old_values, $col);

            // put old first
            if ('new' == array_keys($data)[0]) {
                $data = array_reverse($data);
            }

            $old  = '';
            $new  = '';

            if (array_key_exists('new', $data)) {
                $new = $data['new'];

                if (is_array($new)) {
                    ksort($new);
                    $multi[$col]['new'] = $new;
                }
            }

            if (array_key_exists('old', $data)) {
                $old = $data['old'];

                if (is_array($old)) {
                    ksort($old);
                    $multi[$col]['old'] = $old;
                }
            }

            if (is_array($new) || is_array($old) || (empty($old) && empty($new))) {
                continue;
            }

            $str .= "<p class=\"title\">$col</p>";
            $str .= $this->renderDiff($old, $new);
        }

        // if multi locale
        if ($multi) {
            foreach ($multi as $col => $data) {
                // make sure we have both "old & new"
                if (!array_key_exists('new', $data)) {
                    $data['new'] = array_fill_keys(array_keys($data['old']), null);
                }

                if (!array_key_exists('old', $data)) {
                    $data['old'] = array_fill_keys(array_keys($data['new']), null);
                }

                // make sure the keys count is the same in both "old & new"
                $new_keys = array_keys($data['new']);
                $old_keys = array_keys($data['old']);

                if (count($new_keys) > count($old_keys)) {
                    foreach (array_diff($new_keys, $old_keys) as $key) {
                        $data['old'][$key] = null;
                    }
                }

                // put old first
                if ('new' == array_keys($data)[0]) {
                    $data = array_reverse($data);
                }

                // combine old & new of each key
                $output = [];
                array_walk_recursive($data, function ($value, $key) use (&$output) {
                    $output[$key][] = $value;
                });

                // avoid duplicating the main title on each iteration in case column have sub keys
                $exist = [];

                // render
                foreach ($output as $e => $v) {
                    $old     = isset($v[0]) ? $v[0] : '';
                    $new     = isset($v[1]) ? $v[1] : '';

                    if ($res = $this->renderDiff($old, $new)) {
                        if (!in_array($col, $exist)) {
                            $str .= "<p class=\"title crumbs\"><span></span>$col</p>";
                        }

                        $str .= "<p class=\"title is-5 crumbs\"><span></span>$e</p>";
                        $str .= $res;

                        $exist[] = $col;
                    }
                }
            }
        }

        return $str;
    }

    /**
     * Compares two strings or string arrays, and return their differences.
     * This is a wrapper of the [phpspec/php-diff].
     *
     * @param string|array $old the first string or string array to be compared. If it is a string,
     *                          it will be converted into a string array by breaking at newlines.
     * @param string|array $new the second string or string array to be compared. If it is a string,
     *                          it will be converted into a string array by breaking at newlines.
     *
     * @return string the comparison result
     */
    protected function renderDiff($old, $new)
    {
        if (!is_array($old)) {
            $old = explode("\n", $old);
        }
        if (!is_array($new)) {
            $new = explode("\n", $new);
        }

        foreach ($old as $i => $line) {
            $old[$i] = rtrim($line, "\r\n");
        }
        foreach ($new as $i => $line) {
            $new[$i] = rtrim($line, "\r\n");
        }

        $old = preg_replace('/(?<=data:image.{65}).*?"/', '..."', $old);
        $new = preg_replace('/(?<=data:image.{65}).*?"/', '..."', $new);

        $diff = new \Diff($old, $new);

        return $diff->render(new \Diff_Renderer_Html_SideBySide());
    }
}
