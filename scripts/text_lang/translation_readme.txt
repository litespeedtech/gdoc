
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
