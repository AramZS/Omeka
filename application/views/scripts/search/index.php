<table>
    <thead>
        <tr>
            <th>Record</th>
            <th>Relevance</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->results as $result): ?>
        <tr>
            <td><a href="<?php echo $this->url($result['record']->getRecordRoute($result['record']->id)); ?>"><?php echo $result['record_name']; ?></a></td>
            <td><?php echo $result['relevance']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>