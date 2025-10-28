<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8' />
    <title>FullCalendar Custom Date Format (DD-MM-YYYY HH:MM)</title>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        #calendar {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        /* ทำให้เวลาที่กำหนดเองแสดงผลอย่างเหมาะสมในอีเวนต์ */
        .fc-event-main-frame {
            display: block;
            padding: 2px 0;
        }
        .fc-event-time-custom {
            font-size: 0.8em;
            opacity: 0.8;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                // ⚙️ การตั้งค่าหลัก
                initialView: 'dayGridMonth',
                locale: 'th', // ใช้ภาษาไทย
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },

                // 🔗 การดึงข้อมูลจาก PHP
                events: 'fetch_events.php',

                // 🎨 การแสดงผลอีเวนต์
                // เราจะใช้ eventContent เพื่อกำหนดรูปแบบ DD-MM-YYYY HH:MM ภายในอีเวนต์
                eventContent: function(arg) {
                    // ตรวจสอบว่ามี arg.event.start (DateTime Object) หรือไม่
                    if (arg.event.start) {

                        // **ส่วนสำคัญ:** ใช้ formatDate() ของ FullCalendar เพื่อจัดรูปแบบตามที่คุณต้องการ
                        let startTime = FullCalendar.formatDate(arg.event.start, {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false // 24-hour clock (HH)
                        });

                        // แปลงฟอร์แมตจาก YYYY/MM/DD เป็น YYYY-MM-DD
                        // และปรับเพื่อให้ได้ DD-MM-YYYY HH:MM
                        // FullCalendar's date formatting is locale-dependent, so we enforce the structure:
                        // Use replace() to force the DD-MM-YYYY separator
                        startTime = startTime.replace(/\//g, '-').split(',')[0].trim();
                        let [datePart, timePart] = startTime.split(' '); // แยกวันที่และเวลา

                        // จัดรูปแบบขั้นสุดท้ายเป็น DD-MM-YYYY HH:MM
                        let formattedDate = '';
                        if (datePart && timePart) {
                            let dateParts = datePart.split('-');
                            if (dateParts.length === 3) {
                                // สมมติว่า formatDate ให้ผลลัพธ์เป็น YYYY-MM-DD หรือ DD/MM/YYYY
                                // เราใช้ regex /[^0-9]/g เพื่อให้ได้แค่ตัวเลข แล้วจัดเรียงใหม่
                                const numbers = datePart.match(/\d+/g);
                                if (numbers.length >= 3) {
                                    formattedDate = numbers[2] + '-' + numbers[1] + '-' + numbers[0]; // DD-MM-YYYY
                                }
                            }
                            formattedDate = (formattedDate || datePart) + ' ' + timePart; // รวมเวลา
                        } else {
                            formattedDate = startTime; // ใช้ค่าเริ่มต้นหากแยกไม่ได้
                        }

                        // สร้างองค์ประกอบ HTML ใหม่เพื่อแสดงผล
                        return {
                            html: '<div class="fc-event-main-frame">' +
                                '<div class="fc-event-title">' + arg.event.title + '</div>' +
                                '<div class="fc-event-time-custom">' + formattedDate + '</div>' +
                                '</div>'
                        };

                    } else {
                        // สำหรับ All-Day Events หรือ Event ที่ไม่มีเวลา
                        return {
                            html: '<div class="fc-event-main-frame">' +
                                '<div class="fc-event-title">' + arg.event.title + '</div>' +
                                '</div>'
                        };
                    }
                },

                // ⏰ กำหนดฟอร์แมตสำหรับ Time Grid Axis (มุมซ้ายใน Week/Day View)
                slotLabelFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false // HH:MM (24-hour)
                }
            });

            calendar.render();
        });
    </script>
</head>
<body>

<div id='calendar'></div>

</body>
</html>