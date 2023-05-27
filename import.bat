setlocal

rem @echo off

rem フォルダ構造について vscode-portable(data / vscode.zip)の中にbuild-envというフォルダを作成し、これらの中にbatを格納

rem JISコードをUTF-8に適用
chcp 65001

rem 1.ユーザーデータをCドライブ上(vscode.tmp)へコピーする (その際、Codeはuser-dataに名前変更)

rem 環境変数(USERNAME)からユーザー名を呼び出し、ファイルパスを補完
robocopy C:\Users\%USERNAME%\AppData\Roaming\Code C:\vscode.tmp\data\user-data /E
robocopy C:\Users\%USERNAME%\.vscode\extensions C:\vscode.tmp\data\extensions /E

rem cd /D でドライブをまたいだパス移動
cd /D C:\vscode.tmp

rem この段階で、ユーザーデータファイルはdata内にuser-dataとextensionの２種類が存在する

rem 2.dataをCドライブ上で圧縮する (不要なごみ(圧縮前)は処分)

powershell Compress-Archive data data.zip
rem (powershell Compress-Archive -Path 圧縮前名称 -DestinationPath 圧縮後名称 -Force)

rem /s /qで確認メッセージを無視してフォルダ内データごと削除
rd /s /q data

rem 3.dataをUSBへ移動する

set /p drive="あなたの使っているUSBのドライブを教えてください [アルファベット１文字で回答]"

rem ルートディレクトリまで戻る
cd \
robocopy C:\vscode.tmp %drive%:\vscode-portable /MOVE

endlocal