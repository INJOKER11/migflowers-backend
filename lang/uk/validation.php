<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Поле :attribute повинно бути прийнятим.',
    'accepted_if' => 'Поле :attribute повинно бути прийнятим, якщо :other дорівнює :value.',
    'active_url' => 'Поле :attribute повинно бути дійсною URL-адресою.',
    'after' => 'Поле :attribute повинно містити дату, що йде після :date.',
    'after_or_equal' => 'Поле :attribute повинно містити дату, що йде після або дорівнює :date.',
    'alpha' => 'Поле :attribute повинно містити лише літери.',
    'alpha_dash' => 'Поле :attribute повинно містити лише літери, цифри, дефіси та підкреслення.',
    'alpha_num' => 'Поле :attribute повинно містити лише літери та цифри.',
    'any_of' => 'Поле :attribute є недійсним.',
    'array' => 'Поле :attribute повинно бути масивом.',
    'ascii' => 'Поле :attribute повинно містити лише однобайтові алфавітно-цифрові символи та знаки.',
    'before' => 'Поле :attribute повинно містити дату, що йде до :date.',
    'before_or_equal' => 'Поле :attribute повинно містити дату, що йде до або дорівнює :date.',
    'between' => [
        'array' => 'Поле :attribute повинно містити від :min до :max елементів.',
        'file' => 'Поле :attribute повинно бути від :min до :max кілобайт.',
        'numeric' => 'Поле :attribute повинно бути від :min до :max.',
        'string' => 'Поле :attribute повинно містити від :min до :max символів.',
    ],
    'boolean' => 'Поле :attribute повинно бути true або false.',
    'can' => 'Поле :attribute містить неприпустиме значення.',
    'confirmed' => 'Підтвердження поля :attribute не збігається.',
    'contains' => 'У полі :attribute відсутнє обов\'язкове значення.',
    'current_password' => 'Пароль невірний.',
    'date' => 'Поле :attribute повинно бути дійсною датою.',
    'date_equals' => 'Поле :attribute повинно бути датою, що дорівнює :date.',
    'date_format' => 'Поле :attribute не відповідає формату :format.',
    'decimal' => 'Поле :attribute повинно мати :decimal десяткових знаків.',
    'declined' => 'Поле :attribute повинно бути відхиленим.',
    'declined_if' => 'Поле :attribute повинно бути відхиленим, якщо :other дорівнює :value.',
    'different' => 'Поля :attribute та :other повинні відрізнятися.',
    'digits' => 'Поле :attribute повинно складатися з :digits цифр.',
    'digits_between' => 'Поле :attribute повинно складатися від :min до :max цифр.',
    'dimensions' => 'Поле :attribute має недопустимі розміри зображення.',
    'distinct' => 'Поле :attribute містить дубльоване значення.',
    'doesnt_contain' => 'Поле :attribute не повинно містити жодного з наступних значень: :values.',
    'doesnt_end_with' => 'Поле :attribute не повинно закінчуватися одним із наступних значень: :values.',
    'doesnt_start_with' => 'Поле :attribute не повинно починатися з одного із наступних значень: :values.',
    'email' => 'Поле :attribute повинно бути дійсною електронною адресою.',
    'encoding' => 'Поле :attribute повинно бути закодоване в :encoding.',
    'ends_with' => 'Поле :attribute повинно закінчуватися одним із наступних значень: :values.',
    'enum' => 'Обране значення поля :attribute є недійсним.',
    'exists' => 'Обране значення поля :attribute є недійсним.',
    'extensions' => 'Поле :attribute повинно мати одне з наступних розширень: :values.',
    'file' => 'Поле :attribute повинно бути файлом.',
    'filled' => 'Поле :attribute повинно мати значення.',
    'gt' => [
        'array' => 'Поле :attribute повинно містити більше ніж :value елементів.',
        'file' => 'Поле :attribute повинно бути більше ніж :value кілобайт.',
        'numeric' => 'Поле :attribute повинно бути більше ніж :value.',
        'string' => 'Поле :attribute повинно бути більше ніж :value символів.',
    ],
    'gte' => [
        'array' => 'Поле :attribute повинно містити :value елементів або більше.',
        'file' => 'Поле :attribute повинно бути більшим ніж або дорівнювати :value кілобайт.',
        'numeric' => 'Поле :attribute повинно бути більшим ніж або дорівнювати :value.',
        'string' => 'Поле :attribute повинно бути більшим ніж або дорівнювати :value символів.',
    ],
    'hex_color' => 'Поле :attribute повинно бути дійсним шістнадцятковим кольором.',
    'image' => 'Поле :attribute повинно бути зображенням.',
    'in' => 'Обране значення поля :attribute є недійсним.',
    'in_array' => 'Поле :attribute повинно існувати в :other.',
    'in_array_keys' => 'Поле :attribute повинно містити принаймні один з наступних ключів: :values.',
    'integer' => 'Поле :attribute повинно бути цілим числом.',
    'ip' => 'Поле :attribute повинно бути дійсною IP-адресою.',
    'ipv4' => 'Поле :attribute повинно бути дійсною IPv4-адресою.',
    'ipv6' => 'Поле :attribute повинно бути дійсною IPv6-адресою.',
    'json' => 'Поле :attribute повинно бути дійсним рядком JSON.',
    'list' => 'Поле :attribute повинно бути списком.',
    'lowercase' => 'Поле :attribute повинно бути в нижньому регістрі.',
    'lt' => [
        'array' => 'Поле :attribute повинно містити менше ніж :value елементів.',
        'file' => 'Поле :attribute повинно бути менше ніж :value кілобайт.',
        'numeric' => 'Поле :attribute повинно бути менше ніж :value.',
        'string' => 'Поле :attribute повинно бути менше ніж :value символів.',
    ],
    'lte' => [
        'array' => 'Поле :attribute не повинно містити більше ніж :value елементів.',
        'file' => 'Поле :attribute повинно бути меншим ніж або дорівнювати :value кілобайт.',
        'numeric' => 'Поле :attribute повинно бути меншим ніж або дорівнювати :value.',
        'string' => 'Поле :attribute повинно бути меншим ніж або дорівнювати :value символів.',
    ],
    'mac_address' => 'Поле :attribute повинно бути дійсною MAC-адресою.',
    'max' => [
        'array' => 'Поле :attribute не повинно містити більше ніж :max елементів.',
        'file' => 'Поле :attribute не повинно бути більше ніж :max кілобайт.',
        'numeric' => 'Поле :attribute не повинно бути більше ніж :max.',
        'string' => 'Поле :attribute не повинно бути більше ніж :max символів.',
    ],
    'max_digits' => 'Поле :attribute не повинно містити більше ніж :max цифр.',
    'mimes' => 'Поле :attribute повинно бути файлом одного з типів: :values.',
    'mimetypes' => 'Поле :attribute повинно бути файлом одного з типів: :values.',
    'min' => [
        'array' => 'Поле :attribute повинно містити щонайменше :min елементів.',
        'file' => 'Поле :attribute повинно бути щонайменше :min кілобайт.',
        'numeric' => 'Поле :attribute повинно бути щонайменше :min.',
        'string' => 'Поле :attribute повинно містити щонайменше :min символів.',
    ],
    'min_digits' => 'Поле :attribute повинно містити щонайменше :min цифр.',
    'missing' => 'Поле :attribute повинно бути відсутнім.',
    'missing_if' => 'Поле :attribute повинно бути відсутнім, якщо :other дорівнює :value.',
    'missing_unless' => 'Поле :attribute повинно бути відсутнім, якщо :other не дорівнює :value.',
    'missing_with' => 'Поле :attribute повинно бути відсутнім, якщо присутнє :values.',
    'missing_with_all' => 'Поле :attribute повинно бути відсутнім, якщо присутні :values.',
    'multiple_of' => 'Поле :attribute повинно бути кратним :value.',
    'not_in' => 'Обране значення поля :attribute є недійсним.',
    'not_regex' => 'Формат поля :attribute є недійсним.',
    'numeric' => 'Поле :attribute повинно бути числом.',
    'password' => [
        'letters' => 'Поле :attribute повинно містити принаймні одну літеру.',
        'mixed' => 'Поле :attribute повинно містити принаймні одну велику та одну малу літеру.',
        'numbers' => 'Поле :attribute повинно містити принаймні одну цифру.',
        'symbols' => 'Поле :attribute повинно містити принаймні один символ.',
        'uncompromised' => 'Вказане значення поля :attribute знайдено серед скомпрометованих у витоках даних. Будь ласка, оберіть інше значення поля :attribute.',
    ],
    'present' => 'Поле :attribute повинно бути присутнім.',
    'present_if' => 'Поле :attribute повинно бути присутнім, якщо :other дорівнює :value.',
    'present_unless' => 'Поле :attribute повинно бути присутнім, якщо :other не дорівнює :value.',
    'present_with' => 'Поле :attribute повинно бути присутнім, якщо присутнє :values.',
    'present_with_all' => 'Поле :attribute повинно бути присутнім, якщо присутні :values.',
    'prohibited' => 'Поле :attribute заборонене.',
    'prohibited_if' => 'Поле :attribute заборонене, якщо :other дорівнює :value.',
    'prohibited_if_accepted' => 'Поле :attribute заборонене, якщо :other прийнято.',
    'prohibited_if_declined' => 'Поле :attribute заборонене, якщо :other відхилено.',
    'prohibited_unless' => 'Поле :attribute заборонене, доки :other не входить у :values.',
    'prohibits' => 'Поле :attribute забороняє присутність :other.',
    'regex' => 'Формат поля :attribute є недійсним.',
    'required' => 'Поле :attribute є обов\'язковим для заповнення.',
    'required_array_keys' => 'Поле :attribute повинно містити записи для: :values.',
    'required_if' => 'Поле :attribute є обов\'язковим, якщо :other дорівнює :value.',
    'required_if_accepted' => 'Поле :attribute є обов\'язковим, якщо :other прийнято.',
    'required_if_declined' => 'Поле :attribute є обов\'язковим, якщо :other відхилено.',
    'required_unless' => 'Поле :attribute є обов\'язковим, доки :other не входить у :values.',
    'required_with' => 'Поле :attribute є обов\'язковим, якщо присутнє :values.',
    'required_with_all' => 'Поле :attribute є обов\'язковим, якщо присутні :values.',
    'required_without' => 'Поле :attribute є обов\'язковим, якщо :values відсутнє.',
    'required_without_all' => 'Поле :attribute є обов\'язковим, якщо жодне з :values не присутнє.',
    'same' => 'Поле :attribute повинно збігатися з :other.',
    'size' => [
        'array' => 'Поле :attribute повинно містити :size елементів.',
        'file' => 'Поле :attribute повинно бути :size кілобайт.',
        'numeric' => 'Поле :attribute повинно дорівнювати :size.',
        'string' => 'Поле :attribute повинно містити :size символів.',
    ],
    'starts_with' => 'Поле :attribute повинно починатися з одного із наступних значень: :values.',
    'string' => 'Поле :attribute повинно бути рядком.',
    'timezone' => 'Поле :attribute повинно бути дійсним часовим поясом.',
    'unique' => 'Значення поля :attribute вже зайняте.',
    'uploaded' => 'Не вдалося завантажити поле :attribute.',
    'uppercase' => 'Поле :attribute повинно бути у верхньому регістрі.',
    'url' => 'Поле :attribute повинно бути дійсною URL-адресою.',
    'ulid' => 'Поле :attribute повинно бути дійсним ULID.',
    'uuid' => 'Поле :attribute повинно бути дійсним UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'customer_name' => 'ім\'я клієнта',
        'customer_email' => 'електронна пошта клієнта',
        'customer_phone' => 'телефон клієнта',
        'delivery_address' => 'адреса доставки',
        'delivery_date' => 'дата доставки',
        'recipient_name' => 'ім\'я отримувача',
        'card_message' => 'текст листівки',
        'payment_method' => 'спосіб оплати',
        'items' => 'товари',
        'items.*.product_id' => 'товар',
        'items.*.quantity' => 'кількість',
    ],

];
