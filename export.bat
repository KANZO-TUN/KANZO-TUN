setlocal

rem @echo off

rem JISコードをUTF-8に適用
chcp 65001

set /p drive="【このPCでの】USBのドライブを教えてください [アルファベット１文字] >>>"

rem 4.USBから別PCのCドライブへvscode.zipとdataを移動する
rem build-envは実行中のファイルも含まれているため、移動しない

rem ルートディレクトリまで戻る
cd \
robocopy %drive%:\vscode-portable C:\vscode-portable /MOVE

rem 5.vscode.zipのみを解凍

rem コマンドの実行はpowershellなので、引用符もそのルールに従い、[""→'']
powershell Expand-Archive -Path C:\vscode-portable\vscode.zip -Destination 'c:\Visual Studio Code  -Portable-'

rem 6.data.zipの解凍先をvscode-portable内に設定し、解凍

powershell Expand-Archive -Path C:\vscode-portable\data.zip -Destination 'c:\Visual Studio Code  -Portable-'

rem 7.解凍ごみをまとめて削除

c:
cd \
rd /s /q C:\vscode-portable

rem 8.ショートカットの作成

rem set /p desktop="vscodeのショートカットをデスクトップ上に作成しますか？ (yes or no)"

rem if %desktop%==yes (
rem なぜかrobocopyが弾かれてしまう
rem robocopy "Visual Studio Code -Portable-\Code.exe" "Users\%USERNAME%\Desktop"
rem ren C:\Users\%USERNAME%\Desktop\Code.exe "Visual Studio Code  -Portable-"
rem )
rem else ()

rem 
rem 9.実行ファイル処分の可否 →batファイルごと消してしまうので、必ず最後に。 

rem call :dispose

rem :dispose
rem set /p dispose="この実行に用いたファイルを処分しますか？ (yes or no)"
rem if %dispose%==yes (
rem echo "Enterキー押下後に削除します。再度、環境構築を実行したい場合はvscode-portableを再ダウンロードしてください"
rem pause
rem f:
rem cd \
rem rd /s /q vscode-portable
rem )
rem if %dispose%==no echo "これら実行ファイルは %drive%\vscode-portable\build-env 内に残っています"
rem else (
rem echo "正しく認識されませんでした。もう一度やり直してください"
rem goto :dispose
rem )

endlocal