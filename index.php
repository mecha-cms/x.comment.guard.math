<?php

namespace x\comment__guard__math\y {
    // Disable this extension if `comment` extension is disabled or removed ;)
    if (!isset($state->x->comment)) {
        return;
    }
    function form($y) {
        \extract($GLOBALS, \EXTR_SKIP);
        if (isset($state->x->user) && \Is::user()) {
            return $y; // Disable the security if current user is logged-in
        }
        $a = \mt_rand(1, 10);
        $b = \mt_rand(1, 10);
        $c = $a > $b ? '-' : '+';
        $_SESSION['comment']['math'] = $a + $b;
        // Find the exact position of the comment button(s)
        if (\is_int($index = \array_search('tasks', \array_keys($y[1])))) {
            // Put the math challenge field exactly before the comment button(s)
            $y[1] = \array_slice($y[1], 0, $index) + ['math' => [
                0 => 'p',
                1 => [
                    0 => [
                        0 => 'label',
                        1 => \To::entity($a . ' ' . $c . ' ' . $b . ' =', true, 0),
                        2 => [
                            'for' => $id = 'f:' . \substr(\uniqid(), 6)
                        ]
                    ],
                    1 => [
                        0 => 'br',
                        1 => false
                    ],
                    2 => [
                        0 => 'span',
                        1 => [
                            0 => [
                                0 => 'input',
                                1 => false,
                                2 => [
                                    'autocomplete' => 'off',
                                    'id' => $id,
                                    'name' => 'comment[math]',
                                    'required' => true,
                                    'style' => 'max-width: 6em; width: 6em;',
                                    'type' => 'text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]] + \array_slice($y[1], $index);
        }
        return $y;
    }
    \Hook::set('y.form.comment', __NAMESPACE__ . "\\form", 10);
}

namespace x\comment__guard__math {
    function route($content, $path, $query, $hash) {
        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            return $content;
        }
        \extract($GLOBALS, \EXTR_SKIP);
        if (isset($state->x->user) && \Is::user()) {
            return $content; // Disable the security if current user is logged-in
        }
        $current = $_POST['comment']['math'] ?? "";
        $prev = $_SESSION['comment']['math'] ?? "";
        if ("" === $current) {
            foreach (['author', 'content', 'email', 'link'] as $v) {
                $_SESSION['form']['comment'][$v] = $_POST['comment'][$v] ?? null;
            }
            \class_exists("\\Alert") && \Alert::error('Please answer the math question!');
            \kick($path . $query . ($hash ?? '#comment'));
        }
        if ("" === $prev || $current !== $prev) {
            foreach (['author', 'content', 'email', 'link'] as $v) {
                $_SESSION['form']['comment'][$v] = $_POST['comment'][$v] ?? null;
            }
            \class_exists("\\Alert") && \Alert::error('Incorrect answer!');
            \kick($path . $query . ($hash ?? '#comment'));
        }
        unset($_POST['comment']['math']); // Remove the value so that it won’t be saved to the comment file
        return $content;
    }
    \Hook::set('route.comment', __NAMESPACE__ . "\\route", 0);
}