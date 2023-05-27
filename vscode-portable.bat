setlocal

@echo off

rem JISコードをUTF-8に適用
chcp 65001

call :read

:read
echo 次の選択肢から操作を選んでください (1 or 2)
set /p operation="[1.このPCからvscodeのデータをコピーする]　[2.このPCにvscodeの環境構築をする]"
if %operation%==1 ( 
        echo それでは、このPCからvscodeのデータをダウンロードします。Enterを押してください
        pause
        call f:\vscode-portable\build-env\package\import.bat
        echo 親機からのダウンロードが完了しました。続けて環境構築先の別PCで、このファイルを再実行してください
        pause
        exit
)
if %operation%==2 (
        echo それでは、このPCにvscodeのデータをアップロードします。Enterを押してください
        pause
        call f:\vscode-portable\build-env\package\export.bat
        echo 環境構築先PCからのダウンロードが完了しました。以上で終了になります。お疲れさまでした
        pause
        exit
)
else (
        echo 正しく認識されませんでした。もう一度やり直してください
        goto :read
)

endlocal
