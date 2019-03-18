
We greatly appreciate anyone who contributes to the translation work. 

===============================
To start translation is simple:
===============================
1. fork github project: https://github.com/litespeedtech/gdoc
2. add your language translation under scripts/text_lang/your_lang. 
3. Send in pull request to merge your translation.

If you are willing to help, please send us request at info@litespeedtech.com.
We'll add you to our slack team.

====================================
Structure under text_lang/your_lang
====================================
1. please use exact file names as in English. This will be easy for future updates/compare.
2. BasicTerm.php under common/ is required file.
3. for .hdoc files, you only need to translate where it's needed.
    For example, an [ITEM] contains fields like below

[ITEM]
ID: 
NAME: English name
NS: LE
REQUIRED: 
APPLY: 1
SINCE: 
SEE_ALSO: 

DESCR: English descr
END_DESCR

SYNTAX: bool
END_SYNTAX

EXAMPLE: 
END_EXAMPLE

TIPS: English tips
END_TIPS

[END_ITEM]

    In your file, you only need to list relevant fileds like:

[ITEM]
ID: // this has to be exact same string
NAME: translated string
NS: LE  // NameSpace, this is required to be same as English, 
        // L for "Web ADC", E for "LSWS Enterprise", O for "Open LiteSpeed".

DESCR: translated descr
END_DESCR

TIPS: translated tips
END_TIPS

[END_ITEM]

    Here in translated file:
    We do not have SYNTAX field, as "bool" is a predefined syntax string in BasicTerm.php.
    We do not have EXAMPLE field, as the original one has no English on it.
    We include TIPS field, as the original one has English tips.
    We do not include fields like REQUIRED, APPLY, SINCE, SEE_ALSO. Those are for English only.

    
    For TBL and PAGE, similarly, you don't need to include CONT, SEE_ALSO. 
    Only ID and NAME (and NS if exists) is required, you can eliminate DESCR and other fields if they are empty.

4. inside text of DESCR, if you want to make paragraph break, use 2 line breaks.

=========================
For OLS Translation

so I would suggest,
1. you common the same files to zh-CN\common and zh-CN\ows (edited) 
2. delete *_QA.doc from common
3. can remove any items that have `NS:E`, `NS:L`, `NS:EL`, basically does not `O` in `NS` string. but if it does not have `NS` line, it may be used, so do not delete those.

For Example

[ITEM]
ID: serverPriority
NAME: Priority
NS: EO
REQUIRED: 
APPLY: 2
SINCE: 
SEE_ALSO: External App {ITEM=ExtApp_Help#extAppPriority}, {ITEM=ServSecurity_Help#CGIPriority}

DESCR: Specifies priority of the server processes. Value ranges from
{val}-20{/} to {val}20{/}. A lower number means higher priority.
END_DESCR

SYNTAX: Integer number
END_SYNTAX

EXAMPLE: 
END_EXAMPLE

TIPS: [P] Usually a higher priority leads to slightly higher
web performance on a busy server. Do not set priority higher than that of database processes.
END_TIPS

[END_ITEM]

In translation text, should be
[ITEM]
ID: serverPriority
NAME: 优先级
NS: EO

DESCR: 指定服务进程的优先级。数值范围从
{val}-20{/} 到 {val}20{/}。数值越小，优先级越高。
END_DESCR

TIPS: [P] 通常在繁忙的服务器上，较高的优先级会得到性能的小幅提升。
不要设置比数据库进程更高的优先级。
END_TIPS

[END_ITEM]

only need ID, NAME, DESCR, TIPS. as SYNTAX are defined in BasicTerms, Example is empty. `NS:` tags need to be carried over and keep same.

You can add a symbolic link of generated tips to the webadmin files, this way if you make any changes, you can generate and view from admin panel.



