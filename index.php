<?php namespace x\comment__guard__math;

// Disable this extension if `comment` extension is disabled or removed ;)
if (!isset($state->x->comment)) {
    return;
}

function route__comment($content, $path, $query, $hash) {
    if ('POST' !== $_SERVER['REQUEST_METHOD']) {
        return $content;
    }
    \extract($GLOBALS, \EXTR_SKIP);
    if (isset($state->x->user) && \Is::user()) {
        return $content; // Disable the security if current user is logged-in
    }
    $can_alert = \class_exists("\\Alert");
    $current = $_POST['comment']['math'] ?? "";
    $prev = $_SESSION['comment']['math'] ?? "";
    if ("" === $current) {
        foreach (['author', 'content', 'email', 'link'] as $v) {
            $_SESSION['form']['comment'][$v] = $_POST['comment'][$v] ?? null;
        }
        $can_alert && \Alert::error('Please answer the math question!');
        \kick($path . $query . ($hash ?? '#comment'));
    }
    if ("" === $prev || $current !== $prev) {
        foreach (['author', 'content', 'email', 'link'] as $v) {
            $_SESSION['form']['comment'][$v] = $_POST['comment'][$v] ?? null;
        }
        $can_alert && \Alert::error('Incorrect answer!');
        \kick($path . $query . ($hash ?? '#comment'));
    }
    unset($_POST['comment']['math']); // Remove the value so that it won’t be saved in the comment file
    return $content;
}

function y__form__comment($y, $lot) {
    \extract($lot, \EXTR_SKIP);
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

\Hook::set('route.comment', __NAMESPACE__ . "\\route__comment", 0);
\Hook::set('y.form.comment', __NAMESPACE__ . "\\y__form__comment", 10);