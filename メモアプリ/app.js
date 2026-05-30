// 初期データ定義とLocalStorageチェック
let tasksData = [
    { id: "t01", name: "リビング掃除", estimatedMinutes: 30, isCompleted: false },
    { id: "t02", name: "キッチン水回り", estimatedMinutes: 45, isCompleted: false },
    { id: "t03", name: "トイレ清掃", estimatedMinutes: 15, isCompleted: false },
    { id: "t04", name: "お風呂掃除", estimatedMinutes: 30, isCompleted: false }
];

const savedData = localStorage.getItem("routineTasks");
if (savedData) {
    tasksData = JSON.parse(savedData);
}

// 描画・計算処理
function renderTasks() {
    $("#task-list").empty();
    
    let uncompletedCount = 0;
    let totalMinutes = 0;
    let remainingMinutes = 0;

    for (let i = 0; i < tasksData.length; i++) {
        const task = tasksData[i];
        
        totalMinutes += task.estimatedMinutes;
        
        if (!task.isCompleted) {
            uncompletedCount++;
            remainingMinutes += task.estimatedMinutes;
        }

        const liClass = task.isCompleted ? "task-item task-completed" : "task-item";
        const btnText = task.isCompleted ? "完了済" : "完了にする";
        const btnDisabled = task.isCompleted ? "disabled" : "";

        let timeStampHtml = "";
        if (task.isCompleted && task.completedAt) {
            timeStampHtml = `<small class="time-stamp">完了時刻: ${task.completedAt}</small>`;
        }

        const html = `
            <li class="${liClass}" id="item-${task.id}">
                <div>
                    <strong>${task.name}</strong><br>
                    <small>予定時間: ${task.estimatedMinutes}分</small>
                    ${timeStampHtml}
                </div>
                <div class="task-actions">
                    <button class="btn btn-check" data-id="${task.id}" ${btnDisabled}>${btnText}</button>
                    <button class="btn-delete" data-id="${task.id}">×</button>
                </div>            
            </li>
        `;
        $("#task-list").append(html);
    }

    $("#total-time").text(totalMinutes);
    $("#remaining-time").text(remainingMinutes);

    if (uncompletedCount === 0 && tasksData.length > 0) {
        $("#btn-end").prop("disabled", false);
    } else {
        $("#btn-end").prop("disabled", true);
    }
}
// --- 業務開始ボタンの処理 ---
$("#btn-start").on("click", function() {
    // 現在時刻の取得
    const now = new Date();
    const hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const startTime = `${hours}:${minutes}`;

    // 開始のアラートを表示
    alert(`本日の業務を開始します。\n開始時刻: ${startTime}\n安全第一で頑張りましょう！`);

    // ボタンのテキストと色を変更し、二度押しできないようにする（ロック）
    $(this).text(`業務中 (開始: ${startTime})`);
    $(this).prop("disabled", true);
    $(this).css("background-color", "#28a745"); // 色を青から緑に変更
});


//  完了ボタンの打刻処理
$("#task-list").on("click", ".btn-check", function() {
    const taskId = $(this).data("id");

    for (let i = 0; i < tasksData.length; i++) {
        if (tasksData[i].id === taskId) {
            tasksData[i].isCompleted = true;
            
            const now = new Date();
            const hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            tasksData[i].completedAt = `${hours}:${minutes}`;
            break;
        }
    }

    localStorage.setItem("routineTasks", JSON.stringify(tasksData));
    renderTasks();
});

// --- 削除ボタンを押した時の処理 ---
$("#task-list").on("click", ".btn-delete", function() {
    // 誤操作を防ぐために確認メッセージを出す
    if (!confirm("このタスクを削除してもよろしいですか？")) {
        return; // 「キャンセル」を押したらここで処理を止める
    }

    // どのタスクの削除ボタンが押されたかIDを取得
    const taskId = $(this).data("id");

    // 配列の中から「クリックされたIDと一致しない」タスクだけを集めて、新しい配列として上書きする
    tasksData = tasksData.filter(function(task) {
        return task.id !== taskId;
    });

    // 減った後の配列をLocalStorageに保存し直す
    localStorage.setItem("routineTasks", JSON.stringify(tasksData));
    
    // 画面を再描画（ここでタスクが消え、予定時間なども再計算する）
    renderTasks();
});


$("#btn-add-task").on("click", function() {
    const taskName = $("#new-task-name").val();
    const taskTime = parseInt($("#new-task-time").val(), 10);

    if (!taskName || isNaN(taskTime) || taskTime <= 0) {
        alert("タスク名と予定時間（正しい数値）を入力してください。");
        return;
    }

    const uniqueId = "t_" + new Date().getTime();
    const newTask = {
        id: uniqueId,
        name: "【追加】" + taskName,
        estimatedMinutes: taskTime,
        isCompleted: false
    };

    tasksData.push(newTask);
    localStorage.setItem("routineTasks", JSON.stringify(tasksData));
    
    $("#new-task-name").val("");
    $("#new-task-time").val("");
    renderTasks();
});

// 5. 業務終了とレポート生成処理
$("#btn-end").on("click", function() {
    if (confirm("すべてのタスクが完了しました。業務を終了して報告しますか？")) {
        
        let reportText = "【本日の業務報告】\n担当: T.S 様 (3LDK)\n\n■ 完了タスク一覧:\n";
        for (let i = 0; i < tasksData.length; i++) {
            const task = tasksData[i];
            reportText += `・${task.name} (${task.estimatedMinutes}分) - 完了時刻: ${task.completedAt}\n`;
        }
        reportText += "\n本日はご利用ありがとうございました！";
        alert(reportText);
        
        localStorage.removeItem("routineTasks");
        
        tasksData = [
            { id: "t01", name: "リビング掃除", estimatedMinutes: 30, isCompleted: false },
            { id: "t02", name: "キッチン水回り", estimatedMinutes: 45, isCompleted: false },
            { id: "t03", name: "トイレ清掃", estimatedMinutes: 15, isCompleted: false },
            { id: "t04", name: "お風呂掃除", estimatedMinutes: 30, isCompleted: false }
        ];
        $("#btn-start")
            .text("業務開始")
            .prop("disabled", false)
            .css("background-color", ""); // CSSで指定した元の青色に戻す
        
        renderTasks();
    }
});

// ページ読み込み時の初回描画
renderTasks();